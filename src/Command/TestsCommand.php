<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class TestsCommand extends Command
{
    // public function execute(Arguments $args, ConsoleIo $io)
    // {
    //     $io->out('Hello world.');
    // }
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->addArgument('digits', [
                'help' => 'The digit to show.',
                'required' => true,
            ])
            ->addOption('yell', [
                'help' => 'Shout the digit.',
                'boolean' => true,
            ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io) {
        $digits = $args->getArgument('digits');
        if ($digits === '') {
            $io->error('Please provide at least one digit.');
            return static::CODE_ERROR;
        }
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
            // $io->out($result);
        }
        $io->out('All combinations:');
        $io->out($result);

        return static::CODE_SUCCESS;
    }
}