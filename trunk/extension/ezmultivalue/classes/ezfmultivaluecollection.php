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

/*! \file ezfmultivaluecollection.php
*/


class ezfMultiValueCollection extends ezfSolrDocumentFieldBase
{
    /**
     * @reimp
     */
    public function isCollection()
    {
        return true;
    }

    /**
     * Get collection data. Returns list of ezfSolrDocumentFieldBase documents.
     *
     * @return array List of ezfSolrDocumentFieldBase objects.
     */
    public function getCollectionData()
    {
        $returnList = array();
        $multivalue = $this->ContentObjectAttribute->attribute( 'content' );

        $returnList[] = new ezfSolrDocumentFieldBase( $this->ContentObjectAttribute );

        foreach( $multivalue->getDefinitionRoot()->getElementsByTagName( 'Field' ) as $fieldElement )
        {
            $returnList[] = new ezfMultiValue( $this->ContentObjectAttribute, $fieldElement );
        }

        return $returnList;
    }

    /**
     * @reimp
     */
    static function getCustomFieldName( eZContentClassAttribute $classAttribute, $options = null )
    {
        $baseName = 'attr_' . str_replace( ' ', '', $classAttribute->attribute( 'identifier' ) );
        $attribyteType = self::getClassAttributeType( $classAttribute );

        if ( is_string( $options ) )
        {
            $options = $classAttribute->attribute( 'content' )->getFieldByIdentifier( $options );
        }

        if ( $options === true )
        {
            $returnList = array();
            foreach( $classAttribute->attribute( 'content' )->getFieldList() as $field )
            {
                $baseName .= '_' . str_replace( ' ', '', $field->getAttribute( 'identifier' ) );
                $attribyteType = self::$ATTRIBUTE_TYPE_MAP[(int)$field->getAttribute( 'type' )];
                $returnList[] =  self::$DocumentFieldName->lookupSchemaName( $baseName,
                                                                             $attribyteType );
            }
            return $returnList;
        }

        if ( $options instanceof DOMElement )
        {
            $baseName .= '_' . str_replace( ' ', '', $options->getAttribute( 'identifier' ) );
            $attribyteType = self::$ATTRIBUTE_TYPE_MAP[(int)$options->getAttribute( 'type' )];
        }
        return self::$DocumentFieldName->lookupSchemaName( $baseName,
                                                           $attribyteType );
    }

    static $ATTRIBUTE_TYPE_MAP = array( eZMultiValue::BOOLEAN => 'boolean',
                                        eZMultiValue::INTEGER => 'int',
                                        eZMultiValue::FLOAT => 'float',
                                        eZMultiValue::TEXT_LINE => 'string',
                                        eZMultiValue::TEXT_FIELD => 'text',
                                        eZMultiValue::FIELDSET => 'text',
                                        eZMultiValue::SINGLE_SELECT => 'text',
                                        eZMultiValue::MULTI_SELECT => 'text' );

}

?>
