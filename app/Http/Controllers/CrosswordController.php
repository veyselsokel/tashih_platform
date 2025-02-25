<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class CrosswordController extends Controller
{
    public function index()
    {
        $puzzleData = [
            'width' => 10,
            'height' => 10,
            'acrossClues' => [
                [
                    'x' => 1,
                    'y' => 1,
                    'clue' => "4 Nisan 1953'te batan denizaltımız (Otorite)"
                ],
                [
                    'x' => 1,
                    'y' => 3,
                    'clue' => "Öğretim ve eğitim sistemi (Vilayet)"
                ]
            ],
            'downClues' => [
                [
                    'x' => 1,
                    'y' => 1,
                    'clue' => "Tayin etme (Bir nota)"
                ],
                [
                    'x' => 3,
                    'y' => 1,
                    'clue' => "Kısa çizgi (İnce perde veya örtü)"
                ]
            ]
        ];

        return Inertia::render('CrosswordPage', [
            'puzzleData' => $puzzleData
        ]);
    }
}