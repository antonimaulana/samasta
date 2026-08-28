<?php

use App\Http\Controllers\RthArController;
use App\Http\Controllers\AduanMasyarakatController;
use App\Http\Controllers\EnsiklopediaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MasukanController;
use App\Http\Controllers\RthController;
use App\Http\Controllers\SurveyKepuasanController;
use App\Http\Controllers\TamanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/beranda', '/')->name('beranda');

Route::get('/rth-kota-batam', [RthController::class, 'index'])->name('rth.index');

Route::get('/rth/{taman}/ar-scan', [RthArController::class, 'showArProfile'])->name('rth.ar-scan');

Route::get('/ensiklopedia', [EnsiklopediaController::class, 'index'])->name('ensiklopedia.index');
Route::get('/ensiklopedia/kuis', [EnsiklopediaController::class, 'quiz'])->name('ensiklopedia.quiz');
Route::get('/ensiklopedia/{slug}', [EnsiklopediaController::class, 'show'])->name('ensiklopedia.show');

Route::get('/taman/peta', [TamanController::class, 'map'])->name('tamans.map');
Route::get('/taman', [TamanController::class, 'index'])->name('tamans.index');
Route::get('/taman/{taman}', [TamanController::class, 'show'])->name('tamans.show');

Route::get('/masukan', [MasukanController::class, 'index'])->name('masukan.index');

Route::get('/aduan-masyarakat', [AduanMasyarakatController::class, 'create'])->name('aduan.create');
Route::post('/aduan-masyarakat', [AduanMasyarakatController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('aduan.store');
Route::get('/aduan-masyarakat/terkirim', [AduanMasyarakatController::class, 'success'])->name('aduan.success');
Route::get('/aduan-masyarakat/cek-status', [AduanMasyarakatController::class, 'checkStatusForm'])->name('aduan.check');
Route::post('/aduan-masyarakat/cek-status', [AduanMasyarakatController::class, 'checkStatus'])
    ->middleware('throttle:5,1')
    ->name('aduan.check.submit');

Route::get('/survey-kepuasan', [SurveyKepuasanController::class, 'create'])->name('survey.create');
Route::post('/survey-kepuasan', [SurveyKepuasanController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('survey.store');
Route::get('/survey-kepuasan/terkirim', [SurveyKepuasanController::class, 'success'])->name('survey.success');
