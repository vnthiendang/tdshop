<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;

class TestsCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Hello world.');
    }

    function letterCombinations($digits) {
        if ($digits === '') return [];
        $map = [
            2 => 'abc',
            3 => 'def',
            4 => 'ghi',
            5 => 'jkl',
            6 => 'mno',
            7 => 'pqrs',
            8 => 'tuv',
            9 => 'wxyz'
        ];
        $result = [''];

        for ($i = 0; $i < strlen($digits); $i++) {
            $digit = $digits[$i];
            if (!isset($map[$digit])) continue;

            $letters = str_split($map[$digit]);
            $newResult = [];

            foreach ($result as $prefix) {
                foreach ($letters as $letter) {
                    $newResult[] = $prefix . $letter;
                }
            }

            $result = $newResult;
            print_r($result);
        }

        return $result;
    }
}