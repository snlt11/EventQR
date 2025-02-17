<?php

namespace App\Helper;

class CodeGenerator
{
    private const CHARACTERS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private const DEFAULT_CODE_LENGTH = 8;

    public static function generate(int $length = self::DEFAULT_CODE_LENGTH): string
    {
        $code = '';
        $characters = self::CHARACTERS;
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $code;
    }
}
