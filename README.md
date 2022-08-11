## Passport Introduction

Laravel Passport provides a full OAuth2 server implementation for your Laravel application in a matter of seconds.

## Passport Install Version Details

Reference the table below for the correct version to use in conjunction with the
version of Laravel you have installed and goldspecdigital/laravel-eloquent-uuid package

| Laravel | Passport |   Uuid   |
| ------- | -------- | -------- |
| `v9.*`  | `v10.*`  |  `v9.0`  |


:warning: **READ ALL THE RELATED DOCUMENT CAREFULLY BEFORE IMPLEMENT PASSPORT ON LARAVEL 9.x**: 


## Note

WE ARE INSTALLING TWO PACKAGE HERE:
1. Implement the goldspecdigital/laravel-eloquent-uuid package for uuid.
2. Implement the laravel/passport package for authentication.


### Step-1 Install UUID Package 

```bash
composer require goldspecdigital/laravel-eloquent-uuid:^9.0
```

### Step 2: Using the Uuid trait In app/Models/User.php 

```php
<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class User extends Authenticatable
{
    use Uuid;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}

```

### Step 3: Update Users Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();  // Primary key.
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

```

### Step 4: Migrate Database 
Hint: Create A Database Backup Before Use

```bash
php artisan migrate:refresh --force
```

### Step 5: Add Passport Package
```bash
composer require laravel/passport
```

### Step 6: Install Passport With Uuid
Type answer to yes
```bash
php artisan passport:install --uuids
```

### Step 7: Customization Passport Migration 
You should customize passport default migrations for uuid case
```bash
php artisan vendor:publish --tag=passport-migrations
```

### Step 8: Update Follwing Database Migrations
2016_06_01_000001_create_oauth_auth_codes_table.php
```php
public function up()
    {
        $this->schema->create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
         // $table->unsignedBigInteger('user_id')->index();
            $table->string('user_id')->index();
            $table->uuid('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });
    }
```

2016_06_01_000002_create_oauth_access_tokens_table.php
```php
public function up()
    {
        $this->schema->create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            // $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->uuid('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });
}
```

2016_06_01_000004_create_oauth_clients_table
```php
public function up()
    {
        $this->schema->create('oauth_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });
}
```

### Step 9: Migrate Database
```bash
php artisan migrate:refresh --force
```

### Step 10: Generate Client ID and Secret:
```bash
php artisan passport:client --personal --no-interaction
```

### Step 11: Update App\Models\User model
```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens; //Here
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuid;


    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

    
```

### Step 12: Configuration App\Providers\AuthServiceProvider.php

```php
<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
         'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }
    }
}
    
```
### Step 13: Finally config/auth.php

```php

 'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
 
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
  ],
    
```

### Step 14: Modify database/seeders/DatabaseSeeder.php

```php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::truncate();
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@user.com',
            'password' => Hash::make("12345"),
        ]);
    }
}

```

### Step 15: Create Controller
```bash
php artisan make:controller LoginController
```
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//Feel Free To Visit https://navjotsinghprince.com
class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = 'test@user.com';
        $password = '12345';

        if (Auth::attempt(['email' =>  $email, 'password' =>  $password])) {
            $user = Auth::user();
            $success['access_token'] =  $user->createToken('PrinceFerozepuria')->accessToken;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();
        $response = [
            "user" =>  $user,
            "message" => "success"
        ];
        return response()->json($response, 200);
    }
}

```

### Step 16: Create Routes routes/api.php
```php
<?php

Route::post('login', [LoginController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('getUser', [LoginController::class, 'getUser']);
});

```

### Step 17: Run
```bash
php artisan db:seed
php artisan optimize:clear
composer dump-autoload
php artisan serve
```

## Authors

* :point_right: [Navjot Singh Prince](https://github.com/navjotsinghprince)

See also the site of [contributor](https://navjotsinghprince.com)
who participated in this project.

## Contact US

If you discover any question within passport, please send an e-mail to Prince Ferozepuria via [fzr@navjotsinghprince.com](mailto:fzr@navjotsinghprince.com). Your all questions will be answered.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md)
file for details.