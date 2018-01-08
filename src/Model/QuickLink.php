<?php

namespace CWP\CWP\Model;

use CWP\CWP\PageTypes\BaseHomePage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;

class Quicklink extends DataObject
{
    private static $db = [
        'Name' => 'Varchar(255)',
        'ExternalLink' => 'Varchar(255)',
        'SortOrder' => 'Int',
    ];

    private static $has_one = [
        'Parent' => BaseHomePage::class,
        'InternalLink' => SiteTree::class,
    ];

    private static $summary_fields = [
        'Name' => 'Name',
        'InternalLink.Title' => 'Internal Link',
        'ExternalLink' => 'External Link',
    ];

    private static $table_name = 'Quicklink';

    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Name'] = _t(__CLASS__ . '.NameLabel', 'Name');
        $labels['ExternalLink'] = _t(__CLASS__ . '.ExternalLinkLabel', 'External Link');
        $labels['SortOrder'] = _t(__CLASS__ . '.SortOrderLabel', 'Sort Order');
        $labels['ParentID'] = _t(__CLASS__ . '.ParentRelationLabel', 'Parent');
        $labels['InternalLinkID'] = _t(__CLASS__ . '.InternalLinkLabel', 'Internal Link');

        return $labels;
    }

    public function getLink()
    {
        if ($this->ExternalLink) {
            $url = parse_url($this->ExternalLink);

            // if no scheme set in the link, default to http
            if (!isset($url['scheme'])) {
                return 'http://' . $this->ExternalLink;
            }

            return $this->ExternalLink;
        } elseif ($this->InternalLinkID) {
            return $this->InternalLink()->Link();
        }
    }

    public function canCreate($member = null, $context = [])
    {
        return $this->Parent()->canCreate($member, $context);
    }

    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Parent()->canDelete($member);
    }

    public function canView($member = null)
    {
        return $this->Parent()->canView($member);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('ParentID');

        $externalLinkField = $fields->fieldByName('Root.Main.ExternalLink');

        $fields->removeByName('ExternalLink');
        $fields->removeByName('InternalLinkID');
        $fields->removeByName('SortOrder');
        $externalLinkField->addExtraClass('noBorder');

        $fields->addFieldToTab('Root.Main', CompositeField::create(
            array(
                TreeDropdownField::create(
                    'InternalLinkID',
                    $this->fieldLabel('InternalLinkID'),
                    SiteTree::class
                ),
                $externalLinkField,
                $wrap = CompositeField::create(
                    $extraLabel = LiteralField::create(
                        'NoteOverride',
                        _t(
                            __CLASS__ . '.Note',
                            // @todo remove the HTML from this translation
                            '<div class="message good notice">Note:  If you specify an External Link, '
                            . 'the Internal Link will be ignored.</div>'
                        )
                    )
                )
            )
        ));
        $fields->insertBefore(
            'Name',
            LiteralField::create(
                'Note',
                _t(
                    __CLASS__ . '.Note2',
                    // @todo remove the HTML from this translation
                    '<p>Use this to specify a link to a page either on this site '
                    . '(Internal Link) or another site (External Link).</p>'
                )
            )
        );

        return $fields;
    }
}
