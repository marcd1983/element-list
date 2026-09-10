<?php

namespace Antlion\ElementList\Model;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use DNADesign\Elemental\Extensions\ElementalAreasExtension;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

/**
 * @property int $ElementsID
 * @method ElementalArea Elements()
 */
class ElementList extends BaseElement
{
    private static $icon = 'font-icon-block-layout-2';

    private static $db = [
        'Content'         => 'HTMLText',
        'ColumnCountLarge' => 'Int(4)',
        'ColumnCountMedium' => 'Int(3)',
        'ColumnCountSmall' => 'Int(1)',
        'VerticalAlign'   => "Enum('top,middle,bottom','middle')",
        'HorizontalAlign' => "Enum('left,center,right,justify','left')",
        'NoGridSpace'     => 'Boolean',
    ];

    private static array $has_one = [
        'Elements' => ElementalArea::class
    ];

    private static array $owns = [
        'Elements'
    ];

    private static array $cascade_deletes = [
        'Elements'
    ];

    private static array $cascade_duplicates = [
        'Elements'
    ];

    private static array $extensions = [
        ElementalAreasExtension::class
    ];

    private static string $table_name = 'ElementList';

    private static string $title = 'List';

    private static string $description = 'Orderable list of blocks contained in a block grid';

    private static string $singular_name = 'BlockGrid';

    private static string $plural_name = 'BlockGrids';

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Block Grid');
    }

    /**
     * @return DBField
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Content',
            'ColumnCountLarge',
            'ColumnCountMedium',
            'ColumnCountSmall',
            'VerticalAlign',
            'HorizontalAlign',
            'NoGridSpace',
        ]);

        // Grab the ElementalArea field added by ElementalAreasExtension so we can reposition it last
        $elementsField = $fields->fieldByName('Root.Main.Elements');
        if ($elementsField) {
            $fields->removeByName('Elements');
        }

        $fields->addFieldsToTab('Root.Main', [
            HTMLEditorField::create('Content', 'Content'),

            FieldGroup::create('Columns per row', [
                $this->columnCountDropdown('ColumnCountLarge', 'Large', 8),
                $this->columnCountDropdown('ColumnCountMedium', 'Medium', 4),
                $this->columnCountDropdown('ColumnCountSmall', 'Small', 2),
            ]),

            FieldGroup::create('Alignment', [
                DropdownField::create('VerticalAlign', 'Vertical alignment', [
                    'top'    => 'Top',
                    'middle' => 'Middle',
                    'bottom' => 'Bottom',
                ]),
                DropdownField::create('HorizontalAlign', 'Horizontal alignment', [
                    'left'    => 'Left',
                    'center'  => 'Center',
                    'right'   => 'Right',
                    'justify' => 'Justify',
                ]),
            ]),
            CheckboxField::create('NoGridSpace', 'Remove grid spacing (no gap between cells)'),
        ]);

        // ElementalArea always last
        if ($elementsField) {
            $fields->addFieldToTab('Root.Main', $elementsField);
        }

        return $fields;
    }
    
    private function columnCountDropdown(string $name, string $title, int $max): DropdownField
    {
        $options = array_combine(range(1, $max), range(1, $max));

        return DropdownField::create($name, $title, $options);
    }

    public function GetListBlockIdentifier()
    {
        $filter = URLSegmentFilter::create();
        $t = $filter->filter($this->Title);
        if(!$t || $t == '-' || $t == '-1') $t = $this->ID;
        return $t;
    }

    public function VerticalAlignClass(): string
    {
        return match ($this->VerticalAlign) {
            'top'    => 'align-top',
            'middle' => 'align-middle',
            'bottom' => 'align-bottom',
            default  => '',
        };
    }

    public function HorizontalAlignClass(): string
    {
        return match ($this->HorizontalAlign) {
            'center'  => 'align-center',
            'right'   => 'align-right',
            'justify' => 'align-justify',
            default   => '',
        };
    }

    public function getSummary(): string
    {
        $count = $this->Elements()->Elements()->Count();
        $suffix = $count === 1 ? 'element': 'elements';

        return 'Contains ' . $count . ' ' . $suffix;
    }

    /**
     * Retrieve a elemental area relation name which this element owns
     *
     * @return string
     */
    public function getOwnedAreaRelationName(): string
    {
        $has_one = $this->config()->get('has_one');

        foreach ($has_one as $relationName => $relationClass) {
            if ($relationClass === ElementalArea::class && $relationName !== 'Parent') {
                return $relationName;
            }
        }

        return 'Elements';
    }

    public function inlineEditable(): bool
    {
        return false;
    }
}
