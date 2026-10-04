<?php

/** @var TestCase|stdClass $this */

use App\Models\Student;
use App\Models\User;
use App\Support\Access\RoleLabel;
use App\Support\Locale;
use Tests\TestCase;

/*
 * The app is offered in the same three languages as the web. SetLocale used
 * to run only in the web group, so every API message was Indonesian whatever
 * the user chose, and a choice made on the web never reached the app.
 */

test('the accept-language header picks the first supported language', function () {
    expect(Locale::fromAcceptLanguage('ar-SA,ar;q=0.9,en;q=0.8'))->toBe('ar')
        ->and(Locale::fromAcceptLanguage('fr-FR,en;q=0.5'))->toBe('en')
        ->and(Locale::fromAcceptLanguage('en;q=0.2,ar;q=0.9'))->toBe('ar')
        ->and(Locale::fromAcceptLanguage('id-ID'))->toBe('id')
        ->and(Locale::fromAcceptLanguage('fr,de'))->toBeNull()
        ->and(Locale::fromAcceptLanguage('ar;q=0'))->toBeNull()
        ->and(Locale::fromAcceptLanguage(null))->toBeNull();
});

test('a guest API call answers in the language the app asks for', function () {
    $credentials = ['email' => 'nobody@sekolah.test', 'password' => 'salah', 'device_name' => 'PHPUnit'];

    $this->postJson('/api/v1/login', $credentials, ['Accept-Language' => 'ar'])
        ->assertUnauthorized()
        ->assertHeader('Content-Language', 'ar')
        ->assertJsonPath('message', 'بيانات الدخول غير صحيحة. يرجى التحقق من البريد الإلكتروني/رقم الطالب وكلمة المرور.');

    $this->postJson('/api/v1/login', $credentials, ['Accept-Language' => 'en-US'])
        ->assertJsonPath('message', 'Invalid credentials. Please check your email/student number and password.');

    // An unsupported language falls back to Indonesian. (Symfony fills in
    // "en-us" when a test request sends no header at all.)
    $this->postJson('/api/v1/login', $credentials, ['Accept-Language' => 'fr-FR'])
        ->assertHeader('Content-Language', 'id')
        ->assertJsonPath('message', 'Kredensial tidak valid. Silakan periksa kembali email/NIS dan password Anda.');
});

test('a signed-in user gets their saved language, whatever the header says', function () {
    $user = User::factory()->create(['role' => 'admin', 'preferred_locale' => 'en']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeaders(['Authorization' => "Bearer {$token}", 'Accept-Language' => 'ar'])
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('message', 'Profile loaded')
        ->assertJsonPath('data.preferred_locale', 'en');
});

test('me reports the saved language, indonesian unless changed', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me')
        ->assertHeader('Content-Language', 'id')
        ->assertJsonPath('data.preferred_locale', 'id');
});

test('the app saves the language on the account, so the web shows it too', function () {
    $user = User::factory()->create(['role' => 'siswa', 'preferred_locale' => 'id']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/me/locale', ['locale' => 'ar'])
        ->assertOk()
        ->assertJsonPath('data.locale', 'ar')
        ->assertJsonPath('message', 'تم تغيير اللغة بنجاح.');

    expect($user->fresh()->preferred_locale)->toBe('ar');

    $this->actingAs($user->fresh())->get('/')->assertSee('dir="rtl"', false);
});

test('only id, en and ar can be saved', function () {
    $user = User::factory()->create(['role' => 'siswa', 'preferred_locale' => 'id']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/me/locale', ['locale' => 'fr'])
        ->assertUnprocessable();

    expect($user->fresh()->preferred_locale)->toBe('id');
});

test('saving a language needs a token', function () {
    $this->putJson('/api/v1/me/locale', ['locale' => 'en'])->assertUnauthorized();
});

test('every language file has the same keys', function () {
    $keys = fn (string $lang) => array_keys(json_decode(file_get_contents(lang_path("{$lang}.json")), true));

    expect($keys('en'))->toEqualCanonicalizing($keys('id'))
        ->and($keys('ar'))->toEqualCanonicalizing($keys('id'));
});

test('role labels follow the language and stay singular', function () {
    $teacher = User::factory()->create(['role' => 'guru', 'preferred_locale' => 'en']);
    $token = $teacher->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me')
        ->assertJsonPath('data.role_label', 'Teacher');

    app()->setLocale('id');
    expect(RoleLabel::of('siswa'))->toBe('Siswa')
        ->and(RoleLabel::of('wali_murid'))->toBe('Wali Murid');
});

test('role labels in arabic', function () {
    $teacher = User::factory()->create(['role' => 'teacher', 'preferred_locale' => 'ar']);

    $this->withHeader('Authorization', 'Bearer '.$teacher->createToken('test')->plainTextToken)
        ->getJson('/api/v1/me')
        ->assertJsonPath('data.role_label', 'معلم');
});

test('every roles file has the same keys', function () {
    $keys = fn (string $lang) => array_keys(require lang_path("{$lang}/roles.php"));

    expect($keys('en'))->toBe($keys('id'))
        ->and($keys('ar'))->toBe($keys('id'));
});

test('the discipline status is translated and carries a code that is not', function () {
    $student = User::factory()->create(['role' => 'student', 'preferred_locale' => 'en', 'must_change_password' => false]);
    Student::factory()->create(['user_id' => $student->id]);

    $this->actingAs($student->fresh())->getJson('/api/v1/profile/student/me')
        ->assertOk()
        ->assertJsonPath('data.disiplin_summary.category', 'Safe')
        ->assertJsonPath('data.disiplin_summary.category_code', 'aman');
});
