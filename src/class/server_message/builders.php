<?php

use Helper\Helper;

class AvaiableLanguages
{
    public const PL = 'polish';
    public const ENG = 'english';
    public const SERVER = 'server';
}

abstract class LanguagerBuilder
{

    protected string $file;

    public function choosePhrase(string $Phrase)
    {
        // mozna inaczej pobierac dane z plikow
        return helper::getIniConfiguration($this->file)[$Phrase] ?? $this->getError();

    }

    abstract protected function getError();

}