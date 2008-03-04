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

/*! \file ezfmultivalue.php
*/

/*!
  \class ezfMultiValue ezfmultivalue.php
  \brief The class ezfMultiValue does

*/

class ezfMultiValue extends ezfSolrDocumentFieldBase
{
    /**
     * Constructor
     *
     * @param eZContentObjectAttribute Instance of eZContentObjectAttribute
     * @param DOMElement Field element
     */
    function ezfMultiValue( eZContentObjectAttribute $contentObjectAttribute, DOMElement $field, $value = null )
    {
        $this->ezfSolrDocumentFieldBase( $contentObjectAttribute );
        $this->Field = $field;
        $this->Value = $value;
    }

    /**
     * @reimp
     */
    public function getData()
    {
        $contentClassAttribute = $this->ContentObjectAttribute->attribute( 'contentclass_attribute' );
        $fieldName = self::getFieldName( $contentClassAttribute, $this->Field );

        $multiValue = $this->ContentObjectAttribute->attribute( 'content' );

        if ( $this->Value === null )
        {
            $this->Value = $this->fixupValue( $multiValue->getValueByFieldID( $this->Field->getAttribute( 'field_id' ) ) );
        }
        if ( $this->Value === null )
        {
            return array();
        }

        $classAttributeType = self::getClassAttributeType( $contentClassAttribute );


        $metaData = $this->preProcessValue( $this->Value,
                                            $classAttributeType );

        return array( $fieldName => $metaData );
    }

    /**
     * @reimp
     */
    public function isCollection()
    {
        return ( $this->Value === null &&
                 eZMultiValue::isValueArray( $this->Field ) );
    }

    /**
     * @reimp
     */
    public function getCollectionData()
    {
        $multiValue = $this->ContentObjectAttribute->attribute( 'content' );
        if ( eZMultiValue::isValueArray( $this->Field ) )
        {
            $returnList = array();
            foreach( $multiValue->getValueByFieldID( $this->Field->getAttribute( 'field_id' ) ) as $value )
            {
                $value = $this->fixupValue( $value );
                $returnList[] = new ezfMultiValue( $this->ContentObjectAttribute, $this->Field, $value );
            }
            return $returnList;
        }

        return null;
    }

    /**
     * Correct make and model names.
     *
     * @param string Original value
     *
     * @param string Value
     */
    protected function fixupValue( $value )
    {
        $valueChanged = false;
        if ( !$valueChanged )
        {
            switch( $this->Field->getAttribute( 'type' ) )
            {
                // For singe and multi select, replace value with name of option.
                case eZMultiValue::SINGLE_SELECT:
                case eZMultiValue::MULTI_SELECT:
                {
                    $domDocument = $this->Field->ownerDocument;
                    $xPath = new DOMXPath( $domDocument );
                    $newValue = '';
                    foreach ( $xPath->query( 'Option[@Id=\'' . $value . '\']', $this->Field ) as $optionElement )
                    {
                        $newValue .= ' ' . $optionElement->getAttribute( 'name' );
                        if ( $optionElement->hasAttribute( 'parent' ) )
                        {
                            $parentId = $optionElement->getAttribute( 'parent' );
                            foreach ( $xPath->query( 'Option[@Id=\'' . $parentId . '\']', $this->Field ) as $optionElement2 )
                            {
                                $newValue .= ' ' . $optionElement2->getAttribute( 'name' );
                            }
                        }
                    }
                    if ( $newValue )
                    {
                        $value = $newValue;
                    }
                } break;
            }
        }

        return $value;
    }

    /// Object variables
    protected $Field;
    protected $Value;
}

?>
