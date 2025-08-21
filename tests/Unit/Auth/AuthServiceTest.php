<?php

namespace Tests\Unit\Auth;

use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Helper: bikin fake user yang implement Authenticatable + MustVerifyEmail */
    private function makeUser(bool $verified = true): Authenticatable
    {
        return new class($verified, password_hash('secret', PASSWORD_BCRYPT)) implements Authenticatable, MustVerifyEmail {
            public function __construct(private bool $verified, private string $hash) {}

            public function getAuthIdentifierName()
            {
                return 'id';
            }
            public function getAuthIdentifier()
            {
                return 123;
            }
            public function getAuthPassword()
            {
                return $this->hash;
            }
            public function getAuthPasswordName(): string
            {
                return 'password';
            }
            public function getRememberToken()
            {
                return null;
            }
            public function setRememberToken($value) {}
            public function getRememberTokenName()
            {
                return 'remember_token';
            }

            // MustVerifyEmail
            public function hasVerifiedEmail()
            {
                return $this->verified;
            }
            public function markEmailAsVerified()
            {
                return true;
            }
            public function sendEmailVerificationNotification() {}
            public function getEmailForVerification()
            {
                return 'a@b.com';
            }
        };
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $guard = Mockery::mock(StatefulGuard::class);
        $repo  = Mockery::mock(UserRepository::class);
        $hash  = Mockery::mock(Hasher::class);

        $user = $this->makeUser(verified: true);

        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        $repo->shouldReceive('findByEmail')->once()->with('a@b.com')->andReturn($user);
        $hash->shouldReceive('check')->once()->with('secret', $user->getAuthPassword())->andReturn(true);
        $guard->shouldReceive('login')->once()->with($user, true);
        RateLimiter::shouldReceive('clear')->once();

        $svc = new AuthService($guard, $repo, $hash);
        $res = $svc->login('a@b.com', 'secret', true, '127.0.0.1');

        $this->assertTrue($res['ok']);
    }

    public function test_login_fails_on_wrong_password_and_hits_rate_limiter(): void
    {
        $guard = Mockery::mock(StatefulGuard::class);
        $repo  = Mockery::mock(UserRepository::class);
        $hash  = Mockery::mock(Hasher::class);

        $user = $this->makeUser();

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);
        $repo->shouldReceive('findByEmail')->andReturn($user);
        $hash->shouldReceive('check')->andReturn(false);
        RateLimiter::shouldReceive('hit')->once();

        $svc = new AuthService($guard, $repo, $hash);
        $res = $svc->login('a@b.com', 'oops', false, '127.0.0.1');

        $this->assertFalse($res['ok']);
        $this->assertSame('email_or_password', $res['reason']);
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        $guard = Mockery::mock(StatefulGuard::class);
        $repo  = Mockery::mock(UserRepository::class);
        $hash  = Mockery::mock(Hasher::class);

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->andReturn(30);

        $svc = new AuthService($guard, $repo, $hash);
        $res = $svc->login('a@b.com', 'anything', false, '127.0.0.1');

        $this->assertFalse($res['ok']);
        $this->assertSame('throttled', $res['reason']);
        $this->assertSame(30, $res['seconds']);
    }

    public function test_login_blocks_unverified_users_when_mustverifyemail(): void
    {
        $guard = Mockery::mock(StatefulGuard::class);
        $repo  = Mockery::mock(UserRepository::class);
        $hash  = Mockery::mock(Hasher::class);

        $user = $this->makeUser(verified: false);

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);
        $repo->shouldReceive('findByEmail')->andReturn($user);
        $hash->shouldReceive('check')->andReturn(true);

        $svc = new AuthService($guard, $repo, $hash);
        $res = $svc->login('a@b.com', 'secret', false, '127.0.0.1');

        $this->assertFalse($res['ok']);
        $this->assertSame('unverified', $res['reason']);
    }

    public function test_register_runs_in_transaction_fires_event_and_logs_in(): void
    {
        $guard = Mockery::mock(StatefulGuard::class);
        $repo  = Mockery::mock(UserRepository::class);
        $hash  = Mockery::mock(Hasher::class);

        $user = $this->makeUser();

        // Jangan sentuh DB nyata: jalankan closure transaction langsung
        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) use ($user) {
            // di dalam "transaksi", repo->create dipanggil
            Event::fake();
            return $callback();
        });

        // repo.create mengembalikan $user; hash->make dipanggil
        $repo->shouldReceive('create')->once()->andReturn($user);
        $hash->shouldReceive('make')->once()->with('secret')->andReturn('hashed');

        // setelah commit, guard->login dipanggil
        $guard->shouldReceive('login')->once()->with($user, false);

        $svc = new AuthService($guard, $repo, $hash);
        $out = $svc->register([
            'name' => 'Reza',
            'email' => 'a@b.com',
            'password' => 'secret',
        ]);

        // Jika register() mengembalikan user, assertnya seperti ini:
        if ($out !== null) {
            $this->assertSame($user, $out);
        }

        Event::assertDispatched(Registered::class);
    }
}
