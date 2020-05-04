<?php


use Helper\helper;
use language\Languager;
use language\Serverlanguage;


class LanguagePolish extends LanguagerBuilder implements Languager
{


    public function __construct()
    {
        $this->file = "languages/".  AvaiableLanguages::PL;
    }

    public function getError()
    {
        return "Błąd";
    }
}

class LanguageEnglish extends LanguagerBuilder implements Languager
{


    public function __construct()
    {

        $this->file =  "languages/".  AvaiableLanguages::ENG;
    }

    public function getError()
    {
        return 'Error';
    }
}
class ServerDefault extends LanguagerBuilder implements Languager
{


    public function __construct()
    {

        $this->file = "languages/".  AvaiableLanguages::SERVER;

    }

    public function getError()
    {
        return 'Error';
    }
}
