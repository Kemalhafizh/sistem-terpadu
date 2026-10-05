<?php

use App\Http\Controllers\Student\CollegeCounselingController;
use App\Http\Controllers\Student\InternshipController;
use App\Http\Controllers\Student\MiniBankingController;
use App\Http\Controllers\Student\ShowcaseController;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Facades\Route;

// FASE 118: split per audience. Students use the portals and their own wallet;
// counselling, teachers and finance staff use the monitoring side.
Route::middleware(['auth'])->group(function () {
    Route::prefix('internships')->name('internships.')->group(function () {
        Route::get('/portal', [InternshipController::class, 'portal'])->name('portal')->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES));
        Route::get('/monitoring', [InternshipController::class, 'supervisorMonitoring'])->name('monitoring')
            ->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, 'bk', Roles::LEADERSHIP, 'admin'));
    });
    Route::prefix('college-counseling')->name('college_counseling.')->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES, 'bk'))->group(function () {
        Route::get('/match-engine', [CollegeCounselingController::class, 'matchEngine'])->name('match_engine');
        Route::get('/tracker', [CollegeCounselingController::class, 'myApplications'])->name('tracker');
    });
    // Galeri Karya Siswa — only gallery and my had routes before; create,
    // store, endorse and the public profile were unreachable. The gallery is
    // for the whole school; writing is for students, and StudentShowcasePolicy
    // decides ownership, privacy and who may endorse.
    Route::prefix('showcase')->name('showcase.')->middleware(Roles::only(Roles::MEMBERS))->group(function () {
        $authors = Roles::only(StudentDataAccess::STUDENT_ROLES);
        Route::get('/gallery', [ShowcaseController::class, 'gallery'])->name('gallery');
        Route::get('/my', [ShowcaseController::class, 'myShowcase'])->name('my')->middleware($authors);
        Route::get('/create', [ShowcaseController::class, 'create'])->name('create')->middleware($authors);
        Route::post('/', [ShowcaseController::class, 'store'])->name('store')->middleware($authors);
        Route::get('/students/{user}', [ShowcaseController::class, 'publicProfile'])->name('profile');
        Route::get('/{showcase}', [ShowcaseController::class, 'show'])->name('show');
        Route::get('/{showcase}/edit', [ShowcaseController::class, 'edit'])->name('edit')->middleware($authors);
        Route::put('/{showcase}', [ShowcaseController::class, 'update'])->name('update')->middleware($authors);
        Route::delete('/{showcase}', [ShowcaseController::class, 'destroy'])->name('destroy')->middleware($authors);
        Route::post('/{showcase}/endorse', [ShowcaseController::class, 'endorse'])->name('endorse')->middleware('throttle:20,1');
    });
    Route::prefix('mini-bank')->name('mini_bank.')->group(function () {
        Route::get('/wallet', [MiniBankingController::class, 'walletView'])->name('wallet')->middleware(Roles::only(StudentDataAccess::STUDENT_ROLES));
        Route::get('/teller', [MiniBankingController::class, 'tellerDashboard'])->name('teller')->middleware(Roles::only('staf_tu', Roles::PRINCIPAL, 'admin'));
    });
});
