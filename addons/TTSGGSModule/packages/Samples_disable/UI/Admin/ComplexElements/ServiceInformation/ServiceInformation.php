<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation;

use ModulesGarden\TTSGGSModule\Components\BoldTextWrapper\BoldTextWrapper;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\CopyPasswordInline\CopyPasswordInline;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Image\Image;
use ModulesGarden\TTSGGSModule\Components\ImageText\ImageText;
use ModulesGarden\TTSGGSModule\Components\Label\Label;
use ModulesGarden\TTSGGSModule\Components\Label\LabelPrimary;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Text\TextBold;
use ModulesGarden\TTSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class ServiceInformation extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $table = new TableSimple();

        $table->addColumn(new Column('resource'));
        $table->addColumn(new Column('comparisonType'));
        $table->addColumn(new Column('threshold'));
        $table->addColumn(new Column('actions'));

        $table->addRecord(new Record([
            (new ImageText((new Image())->setUrl("https://picsum.photos/200/300")))->setText("Some Label"),
            "coś",
        ]));
        $table->addRecord(new Record([
            (new ImageText((new Image())->setUrl("/modules/addons/TTSGGSModule/resources/assets//img/logo.png")))->setText("Modules Garden"),
            'myestdomain.com',
            (new VisibilityWrapper(new FormInputText()))->disableWhen('resource', "XXX")
        ]));
        $table->addRecord(new Record([
            (new TextBold())->setText('Memory'),
            '2GB',

        ]));
        $table->addRecord(new Record([
            (new TextBold())->setText('Backups'),
            'Enabled',
        ]));

        $table->addRecord(new Record([
            (new TextBold())->setText('Backups'),
            (new CopyPasswordInline())->setText('MagicPassword'),
        ]));

        $widget = new Widget();
        $widget->addElement($table);
        $widget->setTitle('Service Information');

        $this->addElement($widget);
    }
}