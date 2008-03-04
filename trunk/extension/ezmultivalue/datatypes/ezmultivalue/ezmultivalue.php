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

/*! \file ezmultivalue.php
*/

/**
 * @class eZMultiValue ezmultivalue.php
 *
 * The eZMultiValue class stores the definition of the eZMultiValue.
 * Both data definition and the data are stored in the same XML file. The
 * XML field is defined as followed:
 *
 * <code>
 * <eZMultiValue>
 *   <Definition location="object">
 *     <Field field_id="asda" type="1" placement="0" identifier="number" name="Number" required="true">
 *       <Options min="0" max="65536" default="12">
 *     </Field>
 *     <Field field_id="asdb" type="2" placement="1" identifier="price" name="Price" required="true">
 *       <Options format="%01.2f" default="12.2"/>
 *     </Field>
 *     <Field field_id="asdc" type="3" placement="3" identifier="name" name="Name" required="true">
 *       <Options default="kake" />
 *     </Field>
 *     <Field field_id="asdd" type="4" placement="4" identifier="description" name="Description" required="true">
 *       <Options />
 *     </Field>
 *     <Field field_id="asde" type="5" placement="5" identifier="selects" name="Selects" required="true">
 *       <Options span="2" />
 *     </Field>
 *     <Field field_id="asdf" type="6" placement="6" identifier="county" name="County" required="true">
 *       <Options type="radio" nested="false" />
 *       <Option name="Norway" Id="asdf_1-" value="nor-NO" depth="1" />
 *       <Option name="Sweden" Id="asdf_2-" value="swe-SE" depth="1" />
 *       <Option name="Telemark" Id="asdf_1-1-" parent="asdf_1-" value="telemark" depth="2" />
 *       <Option name="Oslo" Id="asdf_1-2-" parent="asdf_1-" value="oslo" depth="2" />
 *       <Option name="Stockholm" Id="asdf_2-1-" parent="asdf_2-" value="stockholm" depth="2" />
 *     </Field>
 *     <Field field_id="asdg" type="7" placement="7" identifier="make" name="Make" required="true">
 *       <Options source="http://example.com/list.xml" type="list" nested="false" />
 *     </Field>
 *   </Definition>
 *   <Data>
 *     <Value field_id="asda">123</Value>
 *     <Value field_id="asdb">123.21301</Value>
 *     <Value field_id="asdc">Mr2</Value>
 *     <Value field_id="asdd">Toyota MR2</Value>
 *     <Value field_id="asde">Location</Value>
 *     <Value field_id="asdf_2-1-">12</Value>
 *     <Value field_id="asdg">14</Value>
 *   </Data>
 * </code>
 *
 */
class eZMultiValue
{
    /// Consts
    const NONE = 0; // Reserved for none set.
    const INTEGER = 1;
    const FLOAT = 2;
    const TEXT_LINE = 3;
    const TEXT_FIELD = 4;
    const FIELDSET = 5;
    const SINGLE_SELECT = 6;
    const MULTI_SELECT = 7;
    const BOOLEAN = 8;

    const CUSTOM_REDIRECT = 'eZMultiValue_Cust_Redirect';

    const EMPTY_PARENT = 0;


    /**
     * Constructor
     *
     * self::initialize() must be run if objects are created using the
     * constructor. Example:
     * <code>
     * $multiValue = new eZMultiValue();
     * $multiValue->initialize();
     * </code>
     */
    function eZMultiValue()
    {
        $this->DOMDocument = null;
        $this->DOMDefinitionRoot = null;
        $this->DOMValueRoot = null;
    }

	/**
     * Implemented by me @ 8 nov. 2007 because $attribute|attribute(show) called this function
     *
     * @return boolean Has content.
     **/
	public function hasContent()
	{
		return true;
	}

    /**
     * Append field to definition XML
     *
     * @throws Exception if invalid type is provided
     * @param integer Value type
     * @param string Identifier
     * @param string Name
     * @param boolean Required ( optional, default true )
     * @param mixed default value ( optional, default null )
     *
     * @return FieldID
     */
    public function appendField( $type, $identifier, $name, $default, $required = true, array $options = array() )
    {
        if ( !in_array( $type, array_keys( self::fieldTypeNameMap() ) ) )
        {
            throw new Exception( 'Invalid field type: ' . $type );
        }

        if ( empty( $identifier ) )
        {
            $identifier = md5( mt_rand() . '_' . time() );
        }

        // Create field element, and set field attributes.
        $fieldElement = $this->getDOMDocument()->createElement( 'Field' );
        $fieldElement->setAttribute( 'field_id', md5( mt_rand() . '_' . time() ) );
        $fieldElement->setAttribute( 'type', $type );
        $fieldElement->setAttribute( 'placement', $this->getDefinitionRoot()->getElementsByTagName( 'Field' )->length );
        $fieldElement->setAttribute( 'identifier', preg_replace( '@[^A-Za-z0-9]@', '', $identifier ) );
        $fieldElement->setAttribute( 'name', $name );
        $fieldElement->setAttribute( 'required', $required );

        // Create and set options.
        $optionElement = $this->getDOMDocument()->createElement( 'Options' );
        foreach( $options as $name => $value )
        {
            $optionElement->setAttribute( $name, $value );
        }

        // Add new elements to document structure
        $fieldElement->appendChild( $optionElement );
        $this->DOMDefinitionRoot->appendChild( $fieldElement );

        return $fieldElement->getAttribute( 'field_id' );
    }

    /**
     * Set value by field identifier
     *
     * @throw Exception Throws an exception of the identified can not be found in
     * the field definition.
     * @param string Field identifier
     * @param mixed value
     */
    public function setValue( $identifier, $value )
    {
        $fieldElement = $this->getFieldByIdentifier( $identifier );
        if ( !$fieldElement )
        {
            throw new Exception( 'Could not find field with identifier: ' . $identifier . '. Definition: ' . $this->getDOMDocument()->saveXML() );
        }

        $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ), $value );
    }

    /**
     * Set multiple values by field ID
     *
     * @param string Field ID
     * @param array List of values
     */
    protected function setValueListByFieldID( $id, $valueList )
    {
//        var_dump( $id, $valueList );
        foreach( $this->getDOMXPath()->query( '/eZMultiValue/Data/Value[@field_id=\'' . $id . '\']' ) as $valueElement )
        {
            $valueElement->parentNode->removeChild( $valueElement );
        }

        if ( !empty( $valueList ) )
        {
            foreach( $valueList as $value )
            {
                $valueElememt = $this->getDOMDocument()->createElement( 'Value', $value );
                $valueElememt->setAttribute( 'field_id', $id );
                $this->getValueRoot()->appendChild( $valueElememt );
            }
        }
    }

    /**
     * Get Option name by field identifier and option value
     *
     * @param string Field identifier
     * @param string Option value
     *
     * @return string Option name
     */
    public function getOptionName( $fieldIdentifier, $optionValue )
    {
        foreach( $this->getDOMXPath()->query( '/eZMultiValue/Definition/Field[@identifier=\'' . $fieldIdentifier . '\']/Option[@value=\'' . $optionValue . '\']' ) as $optionElement )
        {
            return $optionElement->getAttribute( 'name' );
        }

        return null;
    }

    /**
     * Get value by field identifier
     *
     * @param string Identifier
     *
     * @return mixed Value
     */
    public function getValueByFieldIdentifier( $identifier )
    {
        $field = $this->getFieldByIdentifier( $identifier );
        return $this->getValueByFieldID( $field->getAttribute( 'field_id' ) );
    }

    /**
     * Get value by field ID
     *
     * @param string ID
     *
     * @return mixed Value
     */
    public function getValueByFieldID( $id )
    {
        $elementList = $this->getDOMXPath()->query(
            '/eZMultiValue/Data/Value[@field_id=\'' . $id . '\']' );

        // Return value array
        $field = $this->getFieldByID( $id );
        if ( self::isValueArray( $field ) )
        {
            $returnValueList = array();
            foreach( $elementList as $valueElement )
            {
                $returnValueList[] = $valueElement->nodeValue;
            }
            return $returnValueList;
        }

        // Return single value
        if ( $elementList->length > 0 )
        {
            $value = $elementList->item( 0 )->nodeValue;
            switch( (int)$field->getAttribute( 'type' ) )
            {
                case self::INTEGER:
                case self::FLOAT:
                {
                    if ( trim( $value ) == '' ||
                         !is_numeric( $value ) )
                    {
                        $value = '0';
                    }
                } break;
            }
            return $value;
        }

        return null;
    }

    /**
     * Check if field ID contains array or single value
     *
     * @param DOMElement Field element
     *
     * @return boolean True if field element value is array
     */
    static function isValueArray( DOMElement $field )
    {
        switch( $field->getAttribute( 'type' ) )
        {
            case self::MULTI_SELECT:
            {
                return true;
            } break;

            default:
            {
                return false;
            } break;
        }
    }

    /**
     * Set value by field ID
     *
     * @param string ID
     * @param mixed value
     */
    protected function setValueByFieldID( $id, $value )
    {
        $existingElementList = $this->getDOMXPath()->query(
            '/eZMultiValue/Data/Value[@field_id=\'' . $id . '\']' );
//        var_dump( $id, $value );
        if ( $existingElementList->length === 0 )
        {
            // Create new value element
            $valueElememt = $this->getDOMDocument()->createElement( 'Value', $value );
            $valueElememt->setAttribute( 'field_id', $id );
            $this->getValueRoot()->appendChild( $valueElememt );
        }
        else
        {
            // Change existing value element
            $valueElememt = $existingElementList->item( 0 );
            $valueElememt->nodeValue = '';
            $valueElememt->appendChild( $this->getDOMDocument()->createTextNode( $value ) );
        }

    }

    /**
     * Get field list as NodeList.
     *
     * @return NodeList Field node List
     */
    public function getFieldList()
    {
        return $this->getDefinitionRoot()->getElementsByTagName( 'Field' );
    }

    /**
     * Get field definition by field_id
     *
     * @param string Field id. Returns null if it does not exist.
     *
     * @return DOMElement Field definition
     */
    public function getFieldByID( $fieldID )
    {
        $fieldList = $this->getDOMXPath()->query( 'Field[@field_id=\'' . $fieldID . '\']',
                                                  $this->getDefinitionRoot() );
        if ( $fieldList->length !== 0 )
        {
            return $fieldList->item( 0 );
        }

        return null;
    }

    /**
     * Remove field definition by field_id
     *
     * @param string Field id to remove.
     */
    public function removeFieldByID( $fieldID )
    {
        $fieldList = $this->getDOMXPath()->query( 'Field[@field_id=\'' . $fieldID . '\']',
                                                  $this->getDefinitionRoot() );
        if ( $fieldList->length !== 0 )
        {
            $this->getDefinitionRoot()->removeChild( $fieldList->item( 0 ) );
        }
    }

    /**
     * Get field definition by identifier
     *
     * @param string Field identifier. Returns null if it does not exist.
     */
    public function getFieldByIdentifier( $identifier )
    {
        $fieldList = $this->getDOMXPath()->query( 'Field[@identifier=\'' . $identifier . '\']',
                                                  $this->getDefinitionRoot() );
        if ( $fieldList->length !== 0 )
        {
            return $fieldList->item( 0 );
        }

        return null;
    }

    /**
     * Load eZMultiValue object from XML definition. If the XML provided is
     * empty or invalid, it'll instantiate a new object based on an empty DOMDocument.
     *
     * @param string eZMultiValue XML definition.
     *
     * @return eZMultiValue Instance of eZMultiValue object.
     */
    public static function instantiate( $xml )
    {
        $domDocument = new DOMDocument( "1.0", 'utf8' );

        if ( !empty( $xml ) )
        {
            $domDocument->loadXML( $xml );
        }

        $multiValue = new eZMultiValue();
        $multiValue->setDOMDocument( $domDocument );
        $multiValue->initialize();
        return $multiValue;
    }

    /**
     * Initialize eZMultiValue.
     *
     * Sets internal variables, and prepares the content for access.
     */
    protected function initialize()
    {
        // Get DOMDocument
        $domDocument = $this->getDOMDocument();
        if ( $domDocument === null )
        {
            $domDocument = new DOMDocument( "1.0", 'utf8' );
            $this->setDOMDocument( $domDocument );
        }

        // Set DOMXPath object
        $this->setDOMXPath( new DOMXPath( $domDocument ) );

        // Get the document root DOMElement
        $documentRoot = $domDocument->documentElement;
        if ( !$documentRoot )
        {
            $documentRoot = $domDocument->createElement( 'eZMultiValue' );
            $domDocument->appendChild( $documentRoot );
        }

        // Get and set the definition root.
        $definitionItems = $documentRoot->getElementsByTagName( 'Definition' );
        if ( $definitionItems->length === 0 )
        {
            $definitionRoot = $domDocument->createElement( 'Definition' );
            $documentRoot->appendChild( $definitionRoot );
        }
        else
        {
            $definitionRoot = $definitionItems->item( 0 );
        }
        $this->setDefinitionRoot( $definitionRoot );

        // Get and set the value root.
        $valueItems = $documentRoot->getElementsByTagName( 'Data' );
        if ( $valueItems->length === 0 )
        {
            $valueRoot = $domDocument->createElement( 'Data' );
            $documentRoot->appendChild( $valueRoot );
        }
        else
        {
            $valueRoot = $valueItems->item( 0 );
        }

        $this->setValueRoot( $valueRoot );
    }

    /**
     * Get Value root
     *
     * @return DOMNode Value root
     */
    public function getValueRoot()
    {
        return $this->DOMValueRoot;
    }

    /**
     * Set value root
     *
     * @param DOMNode Value root
     */
    public function setValueRoot( DOMElement $valueRoot )
    {
        $this->DOMValueRoot = $valueRoot;
    }

    /**
     * Get Definition root
     *
     * @return DOMNode Data definition root
     */
    public function getDefinitionRoot()
    {
        return $this->DOMDefinitionRoot;
    }

    /**
     * Set definition root
     *
     * @param DOMNode definition root
     */
    public function setDefinitionRoot( DOMElement $definitionRoot )
    {
        $this->DOMDefinitionRoot = $definitionRoot;
    }

    /**
     * Set DOMDocument
     *
     * @param DOMDocument
     *
     */
    protected function setDOMDocument( DOMDocument $domDocument )
    {
        $this->DOMDocument = $domDocument;
    }

    /**
     * Initialize data. Create data structure and initialize data.
     */
    public function initializeData()
    {
        foreach( $this->getDefinitionRoot()->getElementsByTagName( 'Field' ) as $fieldElement )
        {
            $optionsElement = $fieldElement->getElementsByTagName( 'Options' )->item( 0 );

            // Set default data values.
            if ( $optionsElement->hasAttribute( 'default' ) )
            {
                $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                          $optionsElement->getAttribute( 'default' ) );
            }
        }
    }

    /**
     * Store Data values
     *
     * @param eZHTTPTool eZHTTPTool instance
     * @param string Base attribute name
     *
     * @return array List of warnings.
     */
    public function storeData( eZHTTPTool $http, $baseName )
    {
        $warningList = array();

        foreach( $this->getDefinitionRoot()->getElementsByTagName( 'Field' ) as $fieldElement )
        {
            // Get HTTP Post name and optionsElement.
            $postName = $baseName . '_' . $fieldElement->getAttribute( 'field_id' );
            $optionsElement = $fieldElement->getElementsByTagName( 'Options' )->item( 0 );

            switch( $fieldElement->getAttribute( 'type' ) )
            {
                case self::BOOLEAN:
                {
                    $value = $http->hasPostVariable( $postName ) ? '1' : '0';
                    $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ), $value );
                } break;

                case self::INTEGER:
                {
                    $error = false;
                    $value = (int)$http->postVariable( $postName );
                    if ( $optionsElement->getAttribute( 'enabled' ) )
                    {
                        if ( $value < $optionsElement->getAttribute( 'min' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is less than accepted value: ' . $optionsElement->getAttribute( 'min' );
                        }
                        if ( $value > $optionsElement->getAttribute( 'max' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is larger than accepted value: ' . $optionsElement->getAttribute( 'max' );
                        }
                    }
                    if ( !$error )
                    {
                        $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                                  $value );
                    }
                } break;

                case self::FLOAT:
                {
                    $error = false;
                    $value = (float)$http->postVariable( $postName );
                    if ( $optionsElement->getAttribute( 'enabled' ) )
                    {
                        if ( $value < $optionsElement->getAttribute( 'min' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is less than accepted value: ' . $optionsElement->getAttribute( 'min' );
                        }
                        if ( $value > $optionsElement->getAttribute( 'max' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is larger than accepted value: ' . $optionsElement->getAttribute( 'max' );
                        }
                    }
                    if ( !$error )
                    {
                        $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                                  $value );
                    }
                } break;

                case self::TEXT_LINE:
                {
                    $error = false;
                    $value = $http->postVariable( $postName );
                    if ( $optionsElement->getAttribute( 'enabled' ) )
                    {
                        if ( strlen( $value ) < $optionsElement->getAttribute( 'min' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is less than accepted #characters: ' . $optionsElement->getAttribute( 'min' );
                        }
                        if ( strlen( $value ) > $optionsElement->getAttribute( 'max' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is larger than accepted #characters: ' . $optionsElement->getAttribute( 'max' );
                        }
                    }
                    if ( !$error )
                    {
                        $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                                  $value );
                    }
                } break;

                case self::TEXT_FIELD:
                {
                    $error = false;
                    $value = $http->postVariable( $postName );
                    if ( $optionsElement->getAttribute( 'enabled' ) )
                    {
                        if ( strlen( $value ) < $optionsElement->getAttribute( 'min' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is less than accepted #characters: ' . $optionsElement->getAttribute( 'min' );
                        }
                        if ( strlen( $value ) > $optionsElement->getAttribute( 'max' ) )
                        {
                            $error = true;
                            $warningList[] = 'Value is larger than accepted #characters: ' . $optionsElement->getAttribute( 'max' );
                        }
                    }
                    if ( !$error )
                    {
                        $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                                  $value );
                    }
                } break;

                case self::SINGLE_SELECT:
                {
                    $value = $http->postVariable( $postName );
                    $this->setValueByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                              $value );
                } break;

                case self::MULTI_SELECT:
                {
                    $this->setValueListByFieldID( $fieldElement->getAttribute( 'field_id' ),
                                                  $http->postVariable( $postName ) );
                } break;
            }
        }
    }

    /**
     * Add option to field. The option may only be added to single or multi select fields.
     *
     * @param DOMElement Field
     */
    public function addOptionToField( DOMElement $field )
    {
        $optionNum = $field->getElementsByTagName( 'Option' )->length + 1;
        $optionElement = $this->DOMDocument->createElement( 'Option' );
        $optionElement->setAttribute( 'name', 'Name_' . $optionNum );
        $optionElement->setAttribute( 'Id', md5( mt_rand() . '_' . time() ) );
        $optionElement->setAttribute( 'parent', self::EMPTY_PARENT );
        $optionElement->setAttribute( 'value', 'Value_' . $optionNum );
        $optionElement->setAttribute( 'depth', '1' );
        $field->appendChild( $optionElement );
    }

    /**
     * Get Option element from single select Field by option ID
     *
     * @param DOMElement Field
     * @param string Option ID
     *
     * @return DOMElement Option element. Returns null if no option element is found.
     */
    protected function getOptionFromField( DOMElement $field, $optionID )
    {
        $optionList = $this->getDOMXPath()->query( 'Option[@Id=\'' . $optionID . '\']',
                                                   $field );
        if ( $optionList->length )
        {
            return $optionList->item( 0 );
        }

        return null;
    }

    /**
     * Remove option from field.
     *
     * @param DOMElement Field
     * @param string Option ID
     */
    public function removeOptionFromField( DOMElement $field, $optionID )
    {
        if ( $option = $this->getOptionFromField( $field, $optionID ) )
        {
            $option->parentNode->removeChild( $option );
        }
    }

    /**
     * Store Field definition
     *
     * @param eZHTTPTool eZHTTPTool instance.
     * @param DOMElement Existing field definition.
     *
     * @return array List of warnings.
     */
    public function storeField( eZHTTPTool $http, DOMElement $field )
    {
        $warningList = array();
        $option = $field->getElementsByTagName( 'Options' )->item( 0 );

        // Store general properties
        $identifier = $http->postVariable( 'identifier' );
        if ( $this->getFieldByIdentifier( $identifier ) )
        {
//            $warningList[] = 'Identifier name exists';
            // TODO - fix this
        }

        if ( empty( $identifier ) )
        {
            $identifier = md5( mt_rand() . '_' . time() );
        }

        $field->setAttribute( 'identifier', preg_replace( '@[^A-Za-z0-9]@', '', $identifier ) );
        $field->setAttribute( 'name', $http->postVariable( 'name' ) );
        if ( trim( $http->postVariable( 'name' ) ) == '' )
        {
            $warningList[] = 'Name must be set.';
        }
        $field->setAttribute( 'required', $http->hasPostVariable( 'required' ) ? '1' : '0' );

        switch( (int)$field->getAttribute( 'type' ) )
        {
            case self::BOOLEAN:
            {
                $option->setAttribute( 'default', $http->hasPostVariable( 'default' ) ? '1' : '0' );
            } break;

            case self::TEXT_LINE:
            case self::FLOAT:
            case self::INTEGER:
            {
                $option->setAttribute( 'enabled', $http->hasPostVariable( 'enabled' ) ? '1' : '0' );
                $option->setAttribute( 'default', $http->postVariable( 'default' ) );
                $option->setAttribute( 'min', $http->postVariable( 'min' ) );
                $option->setAttribute( 'max', $http->postVariable( 'max' ) );
            } break;

            case self::TEXT_FIELD:
            {
                $option->setAttribute( 'enabled', $http->hasPostVariable( 'enabled' ) ? '1' : '0' );
                $option->setAttribute( 'default', $http->postVariable( 'default' ) );
                $option->setAttribute( 'min', $http->postVariable( 'min' ) );
                $option->setAttribute( 'max', $http->postVariable( 'max' ) );
                $option->setAttribute( 'rows', $http->postVariable( 'rows' ) );
            } break;

            case self::FIELDSET:
            {
                $option->setAttribute( 'span', $http->postVariable( 'span' ) );
            } break;

            case self::SINGLE_SELECT:
            {
                // XSL upload
                if ( $xslFile = eZHTTPFile::fetch( 'xsl' ) )
                {
                    $xslElementList = $field->getElementsByTagName( 'XSL' );
                    if ( $xslElementList->length )
                    {
                        $xslElement = $xslElementList->item( 0 );
                    }
                    else
                    {
                        $xslElement = $this->getDOMDocument()->createElement( 'XSL' );
                        $field->appendChild( $xslElement );
                    }
                    $option->setAttribute( 'filename', $xslFile->attribute( 'original_filename' ) );
                    $xslElement->nodeValue = base64_encode( file_get_contents( $xslFile->attribute( 'filename' ) ) );
                }

                // Evaluate CSV upload. ( Must be ; separated ).
                if ( $csvFile = eZHTTPFile::fetch( 'csv' ) )
                {
                    $csvElementList = $field->getElementsByTagName( 'CSV' );
                    if ( $csvElementList->length )
                    {
                        $csvElement = $csvElementList->item( 0 );
                    }
                    else
                    {
                        $csvElement = $this->getDOMDocument()->createElement( 'CSV' );
                        $field->appendChild( $csvElement );
                    }
                    $option->setAttribute( 'csv_filename', $csvFile->attribute( 'original_filename' ) );
                    $option->setAttribute( 'csv_ignore_first_line', $http->hasPostVariable( 'CSVIgnoreFirstLine' ) ? 'true' : 'false' );
                    $option->setAttribute( 'csv_delimiter', $http->hasPostVariable( 'CSVDelimiter' ) ? $http->postVariable( 'CSVDelimiter' ) : ';' );
                    $csvElement->nodeValue = base64_encode( file_get_contents( $csvFile->attribute( 'filename' ) ) );
                }

                // Get regular options.
                $option->setAttribute( 'type', $http->postVariable( 'type' ) );
                $option->setAttribute( 'feed_url_1', $http->postVariable( 'feed_url_1' ) );
                if ( !$http->hasPostVariable( 'KeepContent' ) )
                {
                    foreach( $field->getElementsByTagName( 'Option' ) as $optionElement )
                    {
                        $optionID = $optionElement->getAttribute( 'Id' );
                        // If the option has just been added we will not have a name,
                        // so only store for options with HTTP POST variables.
                        if ( $http->postVariable( 'name_' . $optionID ) )
                        {
                            $optionElement->setAttribute( 'name', $http->postVariable( 'name_' . $optionID ) );
                            $optionElement->setAttribute( 'value', $http->postVariable( 'value_' . $optionID ) );
                            $optionElement->setAttribute( 'parent', $http->postVariable( 'parent_' . $optionID ) );
                            if ( $http->postVariable( 'parent_' . $optionID ) == self::EMPTY_PARENT )
                            {
                                $optionElement->setAttribute( 'depth', '1' );
                            }
                            else
                            {
                                $parentElement = $this->getOptionFromField( $field, $http->postVariable( 'parent_' . $optionID ) );
                                $optionElement->setAttribute( 'depth', (int)$parentElement->getAttribute( 'depth' ) + 1 );
                            }
                        }
                    }
                }
            } break;

            case self::MULTI_SELECT:
            {
                // XSL upload
                if ( $xslFile = eZHTTPFile::fetch( 'xsl' ) )
                {
                    $xslElementList = $field->getElementsByTagName( 'XSL' );
                    if ( $xslElementList->length )
                    {
                        $xslElement = $xslElementList->item( 0 );
                    }
                    else
                    {
                        $xslElement = $this->getDOMDocument()->createElement( 'XSL' );
                        $field->appendChild( $xslElement );
                    }
                    $option->setAttribute( 'filename', $xslFile->attribute( 'original_filename' ) );
                    $xslElement->nodeValue = base64_encode( file_get_contents( $xslFile->attribute( 'filename' ) ) );
                }

                // Evaluate CSV upload. ( Must be ; separated ).
                if ( $csvFile = eZHTTPFile::fetch( 'csv' ) )
                {
                    $csvElementList = $field->getElementsByTagName( 'CSV' );
                    if ( $csvElementList->length )
                    {
                        $csvElement = $csvElementList->item( 0 );
                    }
                    else
                    {
                        $csvElement = $this->getDOMDocument()->createElement( 'CSV' );
                        $field->appendChild( $csvElement );
                    }
                    $option->setAttribute( 'csv_filename', $csvFile->attribute( 'original_filename' ) );
                    $option->setAttribute( 'csv_ignore_first_line', $http->hasPostVariable( 'CSVIgnoreFirstLine' ) ? 'true' : 'false' );
                    $option->setAttribute( 'csv_delimiter', $http->hasPostVariable( 'CSVDelimiter' ) ? $http->postVariable( 'CSVDelimiter' ) : ';' );
                    $csvElement->nodeValue = base64_encode( file_get_contents( $csvFile->attribute( 'filename' ) ) );
                }

                $option->setAttribute( 'type', $http->postVariable( 'type' ) );
                $option->setAttribute( 'rows', $http->postVariable( 'rows' ) );
                $option->setAttribute( 'feed_url_1', $http->postVariable( 'feed_url_1' ) );
                if ( !$http->hasPostVariable( 'KeepContent' ) )
                {
                    foreach( $field->getElementsByTagName( 'Option' ) as $optionElement )
                    {
                        $optionID = $optionElement->getAttribute( 'Id' );
                        // If the option has just been added we will not have a name,
                        // so only store for options with HTTP POST variables.
                        if ( $http->postVariable( 'name_' . $optionID ) )
                        {
                            $optionElement->setAttribute( 'name', $http->postVariable( 'name_' . $optionID ) );
                            $optionElement->setAttribute( 'value', $http->postVariable( 'value_' . $optionID ) );
                            $optionElement->setAttribute( 'parent', $http->postVariable( 'parent_' . $optionID ) );
                            if ( $http->postVariable( 'parent_' . $optionID ) == self::EMPTY_PARENT )
                            {
                                $optionElement->setAttribute( 'depth', '1' );
                            }
                            else
                            {
                                $parentElement = $this->getOptionFromField( $field, $http->postVariable( 'parent_' . $optionID ) );
                                $optionElement->setAttribute( 'depth', (int)$parentElement->getAttribute( 'depth' ) + 1 );
                            }
                        }
                    }
                }
            } break;

            default:
            {
                eZDebug::writeError( 'Invalid field type: ' . $field->getAttribute( 'type' ),
                                     'self::storeField()' );
            } break;
        }

        eZDebug::writeNotice( $this->getDOMDocument()->saveXML( $field ),
                              'self::storeField()' );

        return $warningList;
    }

    /**
     * Get class edit template
     *
     * @param integer Field type.
     *
     * @return String Template path
     */
    public function getClassEditTemplate( $fieldType )
    {
        $templateMap = array( self::INTEGER => 'integer.tpl',
                              self::FLOAT => 'float.tpl',
                              self::TEXT_LINE => 'text_line.tpl',
                              self::TEXT_FIELD => 'text_field.tpl',
                              self::FIELDSET => 'fieldset.tpl',
                              self::SINGLE_SELECT => 'single_select.tpl',
                              self::MULTI_SELECT => 'multi_select.tpl',
                              self::BOOLEAN => 'boolean.tpl' );

        switch( $fieldType )
        {
            case self::BOOLEAN:
            case self::INTEGER:
            case self::FLOAT:
            case self::TEXT_LINE:
            case self::TEXT_FIELD:
            case self::FIELDSET:
            case self::SINGLE_SELECT:
            case self::MULTI_SELECT:
            {
                return 'design:ezmultivalue/class_edit/' . $templateMap[(int)$fieldType];
            } break;

            default:
            {
                eZDebug::writeError( 'Invalid field type: ' . $fieldType, 'self::getClassEditTemplate' );
                return null;
            } break;
        }

    }

    /**
     * Get the DOMDocument
     *
     * @return DOMDocument
     */
    public function getDOMDocument()
    {
        return $this->DOMDocument;
    }

    /**
     * Set DOMXPath object
     *
     * @param DOMXPath DOMXPath object
     */
    protected function setDOMXPath( DOMXPath $domXPath )
    {
        $this->DOMXPath = $domXPath;
    }

    /**
     * Get DOMXPath object
     *
     * @return DOMXPath DOMXPath object
     */
    protected function getDOMXPath()
    {
        return $this->DOMXPath;
    }

    /**
     * Get field type name map.
     *
     * @return array Field type name map
     */
    static function fieldTypeNameMap()
    {
        return array( self::INTEGER => ezi18n( 'extension/ezmultivalue/datatypes', 'Integer' ),
                      self::FLOAT => ezi18n( 'extension/ezmultivalue/datatypes', 'Float' ),
                      self::TEXT_LINE => ezi18n( 'extension/ezmultivalue/datatypes', 'Text line' ),
                      self::TEXT_FIELD => ezi18n( 'extension/ezmultivalue/datatypes', 'Text field' ),
                      self::BOOLEAN => ezi18n( 'extension/ezmultivalue/datatypes', 'Boolean' ),
                      self::FIELDSET => ezi18n( 'extension/ezmultivalue/datatypes', 'Fieldset' ),
                      self::SINGLE_SELECT => ezi18n( 'extension/ezmultivalue/datatypes', 'Single select' ),
                      self::MULTI_SELECT => ezi18n( 'extension/ezmultivalue/datatypes', 'Multi select' ) );
    }

    /**
     * Dummy object value field map.
     *
     * @return array Field dummy object value field map.
     */
    static function dummyObjectDataMap()
    {
        return array( self::INTEGER => ezi18n( 'extension/ezmultivalue/datatypes', '123' ),
                      self::FLOAT => ezi18n( 'extension/ezmultivalue/datatypes', '123,456' ),
                      self::TEXT_LINE => ezi18n( 'extension/ezmultivalue/datatypes', 'Lorem ipsum dolor sit amet' ),
                      self::TEXT_FIELD => ezi18n( 'extension/ezmultivalue/datatypes', 'Lorem ipsum dolor sit amet' ),
                      self::BOOLEAN => ezi18n( 'extension/ezmultivalue/datatypes', 'True' ),
                      self::FIELDSET => ezi18n( 'extension/ezmultivalue/datatypes', '' ),
                      self::SINGLE_SELECT => ezi18n( 'extension/ezmultivalue/datatypes', 'Excepteur sint occaecat' ),
                      self::MULTI_SELECT => ezi18n( 'extension/ezmultivalue/datatypes', 'Excepteur sint occaecat, Duis aute irure, Quis autem vel eum iure reprehenderit' ) );
    }

    /**
     * Get dummy field data based on field Type
     *
     * @param string Field type
     *
     * @return string Dummy object data
     */
    static function getDummyObjectDataByFieldType( $fieldType )
    {
        $dummyObjectDataMap = self::dummyObjectDataMap();
        return $dummyObjectDataMap[$fieldType];
    }

    /**
     * Get dummy object data from field identifier
     *
     * @param string Field identifier
     *
     * @return string Dummy object data.
     */
    public function getDummyObjectDataByFieldIdentifier( $fieldIdentifier )
    {
        if ( $fieldElement = $this->getFieldByIdentifier( $fieldIdentifier ) )
        {
            return $this->getDummyObjectDataByFieldType( $fieldElement->getAttribute( 'type' ) );
        }

        return null;
    }

    /**
     * Get field name by type
     *
     * @param integer Field type
     *
     * @return string Field type name
     */
    static function getFieldTypeName( $type )
    {
        $nameMap = self::fieldTypeNameMap();
        return $nameMap[$type];
    }

    /**
     * Attribute name list.
     *
     * @return array List or attribute names.
     */
    public function attributes()
    {
        return array( 'field_type_name_map',
                      'definition',
                      'data',
                      'data_map'
                      );
    }

    /**
     * Export Field definition as CSV
     *
     * @param DOMElement Field DOMElement
     */
    public function exportCSV( $field )
    {
        ob_end_clean();
        header( 'Content-Type: text/plain' );
        $idStack = array();
        $lines = array();
        foreach( $field->getElementsByTagName( 'Option' ) as $optionElement )
        {
            $line = '';
            if ( $optionElement->hasAttribute( 'parent' ) &&
                 $optionElement->getAttribute( 'parent' ) )
            {
                $line = $idStack[$optionElement->getAttribute( 'parent' )] . ';' . $optionElement->getAttribute( 'name' );
            }
            else
            {
                $line = $optionElement->getAttribute( 'name' );
            }
            $idStack[$optionElement->getAttribute( 'Id' )] = $line;
            $lines[] = $line;
        }

        foreach( $lines as $idx => $line )
        {
            foreach( $lines as $line2 )
            {
                if ( strpos( $line2, $line . ';' ) === 0 )
                {
                    unset( $lines[$idx] );
                    break;
                }
            }
        }
        echo implode( "\n", $lines );

        eZExecution::cleanExit();
    }

    /**
     * Evaluate CSV, and populate the field definition with the result
     *
     * @param DOMElement Field DOMElement.
     *
     * @return boolean True if the transformation. False if comething fails.
     */
    public function evaluateCSV( DOMElement $field )
    {
        $option = $field->getElementsByTagName( 'Options' )->item( 0 );
        // Get XSL source
        $csvElementList = $field->getElementsByTagName( 'CSV' );
        if ( !$csvElementList->length )
        {
            eZDebug::writeError( 'No CSV found',
                                 'eZMultiValue::evaluateCSV()' );
            return false;
        }
        $csvElement = $csvElementList->item( 0 );
        if ( !$sourceCSV = base64_decode( $csvElement->nodeValue ) )
        {
            eZDebug::writeError( 'No source CSV found in Field definition: ' . $this->getDOMDocument()->saveXML( $field ),
                                 'eZMultiValue::evaluateCSV()' );
            return false;
        }

        // Perform transformation, and insert it in the field definition.
        $csvDoc = new DOMDocument( '1.0', 'utf8' );
        $rootNode = $csvDoc->createElement( 'root' );
        $csvDoc->appendChild( $rootNode );
        $delimiter = $option->getAttribute( 'csv_delimiter' );
        $lines = explode( "\n", $sourceCSV );
        if ( $option->getAttribute( 'csv_ignore_first_line' ) === 'true' )
        {
            array_shift( $lines );
        }
        $idStack = array( array(), array(), array(), array(), array() );
        foreach( $lines as $line )
        {
            $stack = array();
            $elements = explode( $delimiter, $line );
            foreach( $elements as $idx => $element )
            {
                if ( !empty( $idStack[$idx][$element] ) )
                {
                    // If option already exists, add it to the local stack.
                    $stack[] = $idStack[$idx][$element];
                }
                else
                {
                    $option = $csvDoc->createElement( 'Option' );
                    $option->setAttribute( 'name', $element );
                    $option->setAttribute( 'value', strtolower( $element ) );
                    $option->setAttribute( 'Id', md5( mt_rand() . '_' . $element ) );
                    $option->setAttribute( 'depth', ( $idx + 1 ) );
                    if ( $idx > 0 )
                    {
                        $option->setAttribute( 'parent', $stack[$idx - 1]->getAttribute( 'Id' ) );
                    }
                    $rootNode->appendChild( $option );

                    // Add new element to global and local stack.
                    $idStack[$idx][$element] = $option;
                    $stack[] = $option;
                }
            }
        }

        // Remove existing options.
        $domDocument = $this->getDOMDocument();
        while( $field->getElementsByTagName( 'Option' )->length &&
               $option = $field->getElementsByTagName( 'Option' )->item( 0 ) )
        {
            $field->removeChild( $option );
        }

        // Add new elements.
        foreach( $csvDoc->getElementsByTagName( 'Option' ) as $option )
        {
            $option = $domDocument->importNode( $option, true );
            $field->appendChild( $option );
        }
        return true;
    }

    /**
     * Evaluate XSL, and populate the field definition with the result
     *
     * @param DOMElement Field DOMElement.
     *
     * @return boolean True if the transformation. False if comething fails.
     */
    public function evaluateXSL( $field )
    {
        $option = $field->getElementsByTagName( 'Options' )->item( 0 );
        // Get XSL source
        $xslElementList = $field->getElementsByTagName( 'XSL' );
        if ( !$xslElementList->length )
        {
            eZDebug::writeError( 'No XSL found',
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }
        $xslElement = $xslElementList->item( 0 );
        if ( !$sourceXSL = base64_decode( $xslElement->nodeValue ) )
        {
            eZDebug::writeError( 'No source XSL found in Field definition: ' . $this->getDOMDocument()->saveXML( $field ),
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }

        // Get feed source
        if ( !$feedURL1 = $option->getAttribute( 'feed_url_1' ) )
        {
            eZDebug::writeError( 'No feed URL1 set',
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }
        if ( !$sourceXML = file_get_contents( $option->getAttribute( 'feed_url_1' ) ) )
        {
            eZDebug::writeError( 'Unable to fetch feed: ' . $option->getAttribute( 'feed_url_1' ),
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }

        // Perform transformation, and insert it in the field definition.
        $xslDOM = new DOMDocument( '1.0', 'utf8' );
        if ( !$xslDOM->loadXML( $sourceXSL ) )
        {
            eZDebug::writeError( 'Unable to load XSL document: ' . $sourceXSL,
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }
        $xmlDOM = new DOMDocument( '1.0', 'utf8' );
        if ( !$xmlDOM->loadXML( $sourceXML ) )
        {
            eZDebug::writeError( 'Unable to load XML document: ' . $sourceXML,
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }
        $xslt = new XSLTProcessor();
        $xslt->importStyleSheet( $xslDOM );
        if ( !$transformationDoc = $xslt->transformToDoc( $xmlDOM ) )
        {
            eZDebug::writeError( 'Unable to transform document.',
                                 'eZMultiValue::evaluateXSL()' );
            return false;
        }

        // Remove existing options.
        $domDocument = $this->getDOMDocument();
        while( $field->getElementsByTagName( 'Option' )->length &&
               $option = $field->getElementsByTagName( 'Option' )->item( 0 ) )
        {
            $field->removeChild( $option );
        }

        // Add new elements.
        foreach( $transformationDoc->getElementsByTagName( 'Option' ) as $option )
        {
            $option = $domDocument->importNode( $option, true );
            $field->appendChild( $option );
        }
        return true;
    }

    /**
     * Check if attribute name exists.
     *
     * @param string Attribute name
     *
     * @return boolean True if attribute name exists. False if not.
     */
    public function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() );
    }

    /**
     * Get attribute value
     *
     * @param string Attribute name
     *
     * @return mixed Attribute value. null if attribute does not exist.
     */
    public function attribute( $attr )
    {
        switch( $attr )
        {
            case 'definition':
            {
                return new eZTemplateDOMElement( new eZTemplateDOMDocument( $this->getDOMDocument() ),
                                                 $this->getDefinitionRoot() );
            } break;

            /* Added by me @ 8 nov 2007
             *
             * Allows access by identifier: $attribute.content.data_map.<identifier>
             * eg, $attribute.content.data_map.seller.value|wash
             *
             * */

            case 'data_map':
            {
                $returnList = array();
                $fieldNodeList = $this->getDefinitionRoot()->getElementsByTagName( 'Field' );
                $data = $this->attribute( 'data' );
                if ( $fieldNodeList->length )
                {
                    foreach( $fieldNodeList as $fieldNode )
                    {
						$domField = new eZTemplateDOMElement( new eZTemplateDOMDocument( $this->getDOMDocument() ), $fieldNode );
                        $identifier = $fieldNode->getAttribute( 'identifier' );
                        $valueArray = array(  'field_id' => $fieldNode->getAttribute( 'field_id' ),
                                              'field' => $domField,
                                              'identifier' => $identifier );

                        // If data is empty, return field definition only.
                        if ( empty( $data[$fieldNode->getAttribute( 'field_id' )] ) )
                        {
                            $returnList[$identifier] = $valueArray;
                            continue;
                        }

						$fieldData = $data[$fieldNode->getAttribute( 'field_id' )];

                        $valueArray['name'] = $fieldData['name'];
                        $valueArray['value'] = isset( $fieldData['value'] ) ? $fieldData['value'] : null;
                        $valueArray['valueId'] = isset( $fieldData['valueId'] ) ? $fieldData['valueId'] : null;
                        $valueArray['placement'] = $fieldData['placement'];
                        $valueArray['type'] = $fieldData['type'];

                        // If multi select, populate as array
                        if ( $fieldNode->getAttribute( 'type' ) == self::MULTI_SELECT )
                        {
                            if ( empty( $returnList[$identifier] ) )
                            {
                                $baseArray = $valueArray;
                                unset( $baseArray['value'] );
                                $baseArray['list'] = array();
                                $returnList[$identifier] = $baseArray;
                            }
                            $returnList[$identifier]['list'][] = $valueArray;
                        }
                        else
                        {
                            $returnList[$identifier] = $valueArray;
                        }
                    }
                }
                return $returnList;
            } break;

            case 'data':
            {
                $returnList = array();
                $valueNodeList = $this->getValueRoot()->getElementsByTagName( 'Value' );
                if ( $valueNodeList->length )
                {
                    foreach( $valueNodeList as $valueNode )
                    {
                        $field = $this->getFieldByID( $valueNode->getAttribute( 'field_id' ) );

                        $valueArray = array( 'name' => $field->getAttribute( 'name' ),
                                             'value' => $valueNode->textContent,
                                             'valueId' => $valueNode->textContent,
                                             'placement' => $field->getAttribute( 'placement' ),
                                             'field_id' => $field->getAttribute( 'field_id' ),
                                             'type' => $field->getAttribute( 'type' ),
                                             'field' => new eZTemplateDOMElement( new eZTemplateDOMDocument( $this->getDOMDocument() ),
                                                                                  $field ),
                                             'identifier' => $field->getAttribute( 'identifier' ) );

                        switch( $field->getAttribute( 'type' ) )
                        {
                            // In case of preset value, get value using value Id. if It does not work, asume value == valueId
                            case self::SINGLE_SELECT:
                            case self::MULTI_SELECT:
                            {
                                $parentId = null;
                                $value = null;
                                foreach ( $this->getDOMXPath()->query( 'Option[@Id=\'' . $valueArray['valueId'] . '\']', $field ) as $optionElement )
                                {
                                    $value = $optionElement->getAttribute( 'name' );
                                    if ( $optionElement->hasAttribute( 'parent' ) )
                                    {
                                        $parentId = $optionElement->getAttribute( 'parent' );
                                    }
                                }
                                if ( $value )
                                {
                                    $valueArray['value'] = $value;
                                }
                                $valueArray['parentId'] = $parentId;
                            } break;
                        }

                        switch( $field->getAttribute( 'type' ) )
                        {
                            case self::MULTI_SELECT:
                            {
                                // If multi select, populate as array
                                if ( empty( $returnList[$valueNode->getAttribute( 'field_id' )] ) )
                                {
                                    $baseArray = $valueArray;
                                    unset( $baseArray['value'] );
                                    unset( $baseArray['valueId'] );
                                    $baseArray['list'] = array();
                                    $returnList[$valueNode->getAttribute( 'field_id' )] = $baseArray;
                                }
                                $returnList[$valueNode->getAttribute( 'field_id' )]['list'][] = $valueArray;
                            } break;

                            default:
                            {
                                $returnList[$valueNode->getAttribute( 'field_id' )] = $valueArray;
                            } break;
                        }
                    }
                }
                return $returnList;
            } break;

            case 'field_type_name_map':
            {
                return self::fieldTypeNameMap();
            } break;

            default:
            {
                return null;
            } break;
        }
    }

    /**
     * Store content object version of eZMultiValue
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     */
    public function storeObjectAttribute( eZContentObjectAttribute $contentObjectAttribute )
    {
        $contentObjectAttribute->setAttribute( eZMultiValueType::OBJECT_ATTRIBUTE, $this->xmlString() );
        $contentObjectAttribute->sync();
    }

    /**
     * Get XML string from eZMultiValue definition
     *
     * @return XML string
     */
    public function xmlString()
    {
        return $this->getDOMDocument()->saveXML( $this->getDOMDocument()->documentElement );
    }

    /// Object vars
    protected $DOMDocument;
    protected $DOMDefinitionRoot;
    protected $DOMValueRoot;
    protected $DOMXPath;
}

?>
