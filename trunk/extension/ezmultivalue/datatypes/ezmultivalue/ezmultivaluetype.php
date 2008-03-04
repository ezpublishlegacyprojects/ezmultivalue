<?php
//
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Multivalue
// SOFTWARE RELEASE: 1.0.x
// COPYRIGHT NOTICE: Copyright (C) 2007 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*! \file ezmultivaluetype.php
*/

/*!
  \class eZMultiValueType ezmultivaluetype.php
  \brief The class eZMultiValueType does

*/

class eZMultiValueType extends eZDataType
{
    /// Consts
    const DATA_TYPE_STRING = 'ezmultivalue';
    const CLASS_ATTRIBUTE = 'data_text5';
    const OBJECT_ATTRIBUTE = 'data_text';

    /*!
     Constructor
    */
    function eZMultiValueType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezi18n( 'kernel/classes/datatypes', 'Multi value', 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Store content
    */
    function storeObjectAttribute( $contentObjectAttribute )
    {
    }

    function storeClassAttribute( $contentClassAttribute, $version )
    {
        $contentClassAttribute->setAttribute( eZMultiValueType::CLASS_ATTRIBUTE,
                                              $this->getMultiValue( $contentClassAttribute,
                                                                    eZMultiValueType::CLASS_ATTRIBUTE )->xmlString() );
        $contentClassAttribute->sync();
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return $this->getMultiValue( $contentObjectAttribute, eZMultiValueType::OBJECT_ATTRIBUTE );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return $this->getMultiValue( $contentObjectAttribute, eZMultiValueType::OBJECT_ATTRIBUTE )->hasContent();
    }

    function metaData( $contentObjectAttribute )
    {
        $multiValue = $this->getMultiValue( $contentObjectAttribute, eZMultiValueType::OBJECT_ATTRIBUTE );
        $metaData = '';
        foreach( $multiValue->getDefinitionRoot()->getElementsByTagName( 'Field' ) as $fieldElement )
        {
            if ( eZMultiValue::isValueArray( $fieldElement ) )
            {
                foreach( $multiValue->getValueByFieldID( $fieldElement->getAttribute( 'field_id' ) ) as $value )
                {
                    $metaData .= ' ' . $value;
                }
            }
            else
            {
                $metaData .= ' ' . $multiValue->getValueByFieldID( $fieldElement->getAttribute( 'field_id' ) );
            }
        }
        return $metaData;
    }

    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
		$attribute_identifier = $contentObjectAttribute->attribute('contentclass_attribute_identifier');

		$multiValue = $this->getMultiValue( $contentObjectAttribute, eZMultiValueType::OBJECT_ATTRIBUTE );
        $multiValue->storeData( $http, $base );

        $contentObjectAttribute->setAttribute( eZMultiValueType::OBJECT_ATTRIBUTE, $multiValue->xmlString() );
        $contentObjectAttribute->sync();
    }

    function customObjectAttributeHTTPAction( $http, $action, $contentObjectAttribute, $parameters )
    {
        // TODO
    }

    function title( $contentObjectAttribute, $name = 'name' )
    {
        // TODO
    }

    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        // If first version, copy multivalue definition from eZContentClassAttribute
        if ( $contentObjectAttribute->attribute( 'version' ) == 1 )
        {
            $multiValue = $contentObjectAttribute->attribute( 'contentclass_attribute' )->attribute( 'content' );
            $multiValue->initializeData();
            $contentObjectAttribute->setAttribute( eZMultiValueType::OBJECT_ATTRIBUTE,
                                                   $multiValue->xmlString() );
        }
    }

    function classAttributeContent( $contentClassAttribute )
    {
        return $this->getMultiValue( $contentClassAttribute, eZMultiValueType::CLASS_ATTRIBUTE );
    }

    /*!
     */
    function customClassAttributeHTTPAction( $http, $action, $contentClassAttribute )
    {
        switch( $action )
        {
            case 'update_placement':
            {
                // Create new order in $fieldElementArray, and remove existing elements.
                $fieldElementArray = array();
                $multiValue = $this->getMultiValue( $contentClassAttribute, eZMultiValueType::CLASS_ATTRIBUTE );
                $definitionRoot = $multiValue->getDefinitionRoot();
                foreach( $definitionRoot->getElementsByTagName( 'Field' ) as $fieldElement )
                {
                    $newPlacement = (int)$http->postVariable( 'eZMultiValueType_Placement_' . $fieldElement->getAttribute( 'field_id' ) );
                    while ( !empty( $fieldElementArray[$newPlacement] ) )
                    {
                        ++$newPlacement;
                    }
                    $fieldElement->setAttribute( 'placement', $newPlacement );
                    $fieldElementArray[$newPlacement] = $fieldElement;
                }
                foreach( $fieldElementArray as $fieldElement )
                {
                    $definitionRoot->removeChild( $fieldElement );
                }
                ksort( $fieldElementArray );
                // Insert new elements in correct order.
                foreach( $fieldElementArray as $fieldElement )
                {
                    $definitionRoot->appendChild( $fieldElement );
                }
                $this->storeClassAttribute( $contentClassAttribute, $contentClassAttribute->attribute( 'version' ) );
            } break;

            case 'add_field':
            {
                // Append new temporary field.
                $fieldID = $this->getMultiValue( $contentClassAttribute, eZMultiValueType::CLASS_ATTRIBUTE )->appendField(
                    (int)$http->postVariable( 'eZMultiValueType_FieldType_' . $contentClassAttribute->attribute( 'id' ) ),
                    '',
                    '',
                    '' );
                $this->storeClassAttribute( $contentClassAttribute, $contentClassAttribute->attribute( 'version' ) );
                return $contentClassAttribute->currentModule()->redirect( 'ezmultivalue',
                                                                          'edit_class_field',
                                                                          array( $contentClassAttribute->attribute( 'id' ),
                                                                                 $contentClassAttribute->attribute( 'version' ),
                                                                                 $contentClassAttribute->Module->UserParameters[ 'language' ],
                                                                                 $fieldID ) );
            } break;

            case 'remove_selected':
            {
                if ( $http->hasPostVariable( 'eZMultiValueType_IDArray_' . $contentClassAttribute->attribute( 'id' ) ) )
                {
                    foreach( $http->postVariable( 'eZMultiValueType_IDArray_' . $contentClassAttribute->attribute( 'id' ) ) as $fieldID )
                    {
                        $this->getMultiValue( $contentClassAttribute, eZMultiValueType::CLASS_ATTRIBUTE )->removeFieldByID( $fieldID );
                    }
                }
                $this->storeClassAttribute( $contentClassAttribute, $contentClassAttribute->attribute( 'version' ) );
            } break;

            default:
            {
                eZDebug::writeError( 'Unknown custom class action: ' . $action );
            } break;

        }
    }

    function isIndexable()
    {
        return true;
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
    }

    function fromString( $contentObjectAttribute, $string )
    {
    }

    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
    }

    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
    }

    function serializeContentObjectAttribute( $package, $objectAttribute )
    {
    }

    function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
    }

    /**
     * Get local instance of eZMultiValue.
     *
     * @param eZPersistentObject Object locateion.
     * @param string Value attribute name, eZMultiValueType::CLASS_ATTRIBUTE or eZMultiValueType::OBJECT_ATTRIBUTE
     *
     * @return eZMultiValue Instance of eZMultiValue class.
     */
    protected function getMultiValue( eZPersistentObject $object, $attribute )
    {
        if ( empty( $this->MultiValue[$attribute] ) )
        {
            $this->MultiValue[$attribute] = eZMultiValue::instantiate( $object->attribute( $attribute ) );
        }

        return $this->MultiValue[$attribute];
    }

    /// Local vars
    protected $MultiValue = array();
}

eZDataType::register( eZMultiValueType::DATA_TYPE_STRING, 'ezmultivaluetype' );

?>
