<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation;

use ModulesGarden\TSSGGSModule\Components\BoldTextWrapper\BoldTextWrapper;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\CopyPasswordInline\CopyPasswordInline;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Image\Image;
use ModulesGarden\TSSGGSModule\Components\ImageText\ImageText;
use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Components\Label\LabelPrimary;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TSSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Text\TextBold;
use ModulesGarden\TSSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

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
            (new ImageText((new Image())->setUrl("/modules/addons/TSSGGSModule/resources/assets//img/logo.png")))->setText("Modules Garden"),
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