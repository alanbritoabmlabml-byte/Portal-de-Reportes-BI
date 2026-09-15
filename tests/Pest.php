<?php

use App\Support\Rastro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    // El rastro memoriza la sesión en curso; entre pruebas la base se rehace,
    // así que hay que soltarla o la siguiente escribiría sobre un id muerto.
    ->beforeEach(fn () => Rastro::olvidar())
    ->in('Feature');
