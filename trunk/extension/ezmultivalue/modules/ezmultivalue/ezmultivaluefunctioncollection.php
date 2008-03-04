<?php
//
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZMultivalue
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

/*! \file ezmultivaluefunctioncollection.php
*/

/*!
  \class eZMultiValueFunctionCollection ezmultivaluefunctioncollection.php
  \brief The class eZMultiValueFunctionCollection does

*/

class eZMultiValueFunctionCollection
{
    /**
     * Get Class attribute by class attribute string
     *
     * @param string Class Attribute identifier
     *
     * @return eZContentClassAttribute Instance of eZContentClassAttribute.
     * null if it does not exist.
     */
    public function getClassAttribute( $classAttributeIdentifier )
    {
        $contectClassAttributeID = eZContentObjectTreeNode::classAttributeIDByIdentifier( $classAttributeIdentifier );
        if ( !$contectClassAttributeID )
        {
            eZDebug::writeNotice( 'Could not find Class attribute: ' . $classAttributeIdentifier,
                                  'eZMultiValueFunctionCollection::getClassAttribute()' );
            return array( 'result' => null );
        }
        return array( 'result' => eZContentClassAttribute::fetch( $contectClassAttributeID ) );
    }

    /**
     * Get option name based on option value
     *
     * @param eZContentClassAttribute Instance of eZContentClassAttribute
     * @param string Field identifier
     * @param string Value
     *
     * @return string Option name
     */
    public function getOptionName( eZContentClassAttribute $classAttribute, $fieldIdentifier, $optionValue )
    {
        $multiValue = $classAttribute->attribute( 'content' );

        return array( 'result' => $multiValue->getOptionName( $fieldIdentifier, $optionValue ) );
    }

    /**
     * Get value.
     *
     * @param eZTemplateDOMElement Data element.
     * @param String Field ID
     *
     * @return eZTemplateDOMElement Value node
     */
    public function value( eZTemplateDOMElement $dataElement, $fieldID )
    {
        if ( $dataElement->DOMElement->getElementsByTagName( 'Value' )->length )
        {
            $valueList = $dataElement->TemplateDocument->DOMXPath->query( 'Value[@field_id=\'' . $fieldID . '\']',
                                                                          $dataElement->DOMElement );
            if ( $valueList->length > 0 )
            {
                return array( 'result' => new eZTemplateDOMElement( new eZTemplateDOMDocument( $valueList->item( 0 )->ownerDocument ),
                                                                                               $valueList->item( 0 ) ) );
            }
        }

        return array( 'result' => null );
    }

    /**
     * Get value list.
     *
     * @param eZTemplateDOMElement Data element.
     * @param String Field ID
     *
     * @return array List of eZTemplateDOMElement value nodes
     */
    public function valueList( eZTemplateDOMElement $dataElement, $fieldID )
    {
        $returnArray = array();
        if ( $dataElement->DOMElement->getElementsByTagName( 'Value' )->length )
        {
            $valueList = $dataElement->TemplateDocument->DOMXPath->query( 'Value[@field_id=\'' . $fieldID . '\']',
                                                                          $dataElement->DOMElement );
            if ( $valueList->length > 0 )
            {
                foreach( $valueList as $domValue )
                {
                    $returnArray[] = new eZTemplateDOMElement( new eZTemplateDOMDocument( $domValue->ownerDocument ),
                                                               $domValue );
                }
            }
        }

        return array( 'result' => $returnArray );
    }
}

?>
