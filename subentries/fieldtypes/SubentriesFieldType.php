<?php
/**
 * Subentries plugin for Craft CMS
 *
 * Craft field type plugin that limits listed entries by the current entry's subentries
 *
 * @author    saschame
 * @copyright Copyright (c) 2017 saschame
 * @link      https://github.com/saschame
 * @package   EntriesOfSection
 * @since     1.0.0
 */

namespace Craft;

class SubentriesFieldType extends BaseElementFieldType
{

    private $ownerId;

    protected $elementType = 'Entry';
    /**
     * Returns the name of the fieldtype.
     *
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Subentries');
    }

    public function getInputSelectionCriteria()
    {
        $selectionCriteria = [];

        if ($this->ownerId) {
            $selectionCriteria['childOf'] = $this->ownerId;
        }

        return $selectionCriteria;
    }

    public function getInputTemplateVariables($name, $criteria)
    {
        if (
            $this->elementType === 'Entry' &&
            $this->element &&
            !empty($this->element->elementType) &&
            $this->element->elementType === 'Entry'
        ) {
            $variables = parent::getInputTemplateVariables($name, $criteria);

            $element = $this->element;
            $section = $element->getSection();
            if ($section) {
                $variables['criteria']['sectionId'][] = $section->id;
                $variables['criteria']['descendantOf'] = $element->id;
                $variables['sources'] = ['section:' . $section->id];
            }

            return $variables;
        } else if (
            $this->elementType === 'Entry' &&
            $this->element &&
            !empty($this->element->elementType) &&
            $this->element->elementType === 'SuperTable_Block'
        ) {
            $handle = $this->getClassHandle();
            $owner_id = $this->element->getAttribute('ownerId');
            $owner = $this->element->getOwner();
            $section = $owner->getSection();

            $variables = parent::getInputTemplateVariables($name, $criteria);
            $variables['sourceElementId'] = $owner_id;

            if ($handle === 'Subentries' && $owner) {
                if ($owner->hasDescendants() && $section) {
                    $settings = $this->getSettings();
                    $settings->setAttribute('sources', ['section:' . $section->id]);
                    if (! empty($variables) && ! empty($variables['criteria'])) {
                        $variables['criteria']['descendantOf'] = $owner->id;
                    }
                    $this->setSettings($settings);
                }
            }

            return $variables;
        } else {
            $variables = parent::getInputTemplateVariables($name, $criteria);

            return $variables;
        }
    }
}
