<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\HintsBox\Components;

use ModulesGarden\TTSGGSModule\Components\Hint\Hint;
use ModulesGarden\TTSGGSModule\Components\Hint\HintDanger;
use ModulesGarden\TTSGGSModule\Components\Hint\HintDefault;
use ModulesGarden\TTSGGSModule\Components\Hint\HintInfo;
use ModulesGarden\TTSGGSModule\Components\Hint\HintSuccess;
use ModulesGarden\TTSGGSModule\Components\Hint\HintWarning;
use ModulesGarden\TTSGGSModule\Components\HintsBox\HintsBox as HintsBosComponent;

class HintsBox extends HintsBosComponent
{
    public function loadHtml(): void
    {
        $this->setTitle("Some useless HintBox");

        $hint0 = new Hint();
        $hint0->setTitle("Hint0 Title No Style Accept HTML");
        $hint0->setDescription("<ul><li>1. Some item</li><li>2. Good item</li><li>3. Not good item</li></ul>");

        $hint1 = new HintDefault();
        $hint1->setTitle("Hint1 Title Default Style");
        $hint1->setDescription("There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunk");

        $hint2 = new HintSuccess();
        $hint2->setTitle("Hint2 Title Success Style");
        $hint2->setDescription("There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunk");

        $hint3 = new HintInfo();
        $hint3->setTitle("Hint3 Title Info Style");
        $hint3->setDescription("There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunk");

        $hint4 = new HintWarning();
        $hint4->setTitle("Hint2 Title Style Warning");
        $hint4->setDescription("There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunk");

        $hint5 = new HintDanger();
        $hint5->setTitle("Hint3 Title Style Danger");
        $hint5->setDescription("There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunk");

        $this->addHint($hint0);
        $this->addHint($hint1);
        $this->addHint($hint2);
        $this->addHint($hint3);
        $this->addHint($hint4);
        $this->addHint($hint5);
    }
}