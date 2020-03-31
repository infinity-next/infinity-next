<?php

Route::get('login', 'LoginController@showLoginForm')->name('login');
Route::post('login', 'LoginController@login')->name('login.attempt');

Route::get('logout', 'LoginController@logout')->name('logout');

Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'RegisterController@register')->name('register.attempt');

Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');

Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
