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

/*! \file edit_class_field.php
*/

$Module = $Params['Module'];
require_once( "kernel/common/template.php" );
$http = eZHTTPTool::instance();

$classAttributeID = $Params['ClassAttributeID'];
$language = $Params['LanguageCode'];
$version = $Params['Version'];
$fieldID = $Params['FieldID'];

// Check that content class attribute exists.
$contentClassAttribute = eZContentClassAttribute::fetch( $classAttributeID, true, $version );
if ( !$contentClassAttribute )
{
    eZDebug::writeError( 'Could not fetch eZContentClassAttribute, id: ' . $classAttributeID . ', version:' . $version );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

// Check that field exists.
$multiValueDataType = $contentClassAttribute->attribute( 'data_type' );
$multiValue = $multiValueDataType->classAttributeContent( $contentClassAttribute );
$field = $multiValue->getFieldByID( $fieldID );
if ( !$field )
{
    eZDebug::writeError( 'Could not get Field, field_id: ' . $field );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$storeAction = ( $http->hasPostVariable( 'StoreButton' ) ||
                 $http->hasPostVariable( 'AddOption' ) ||
                 $http->hasPostVariable( 'RemoveOptions' ) ||
                 $http->hasPostVariable( 'Evaluate' ) ||
                 $http->hasPostVariable( 'EvaluateCSV' ) ||
                 $http->hasPostVariable( 'ExportCSV' ) );

// Handle single select custom HTTP input
if ( $http->hasPostVariable( 'AddOption' ) )
{
    $multiValue->addOptionToField( $field );
}
if ( $http->hasPostVariable( 'RemoveOptions' ) &&
     $http->hasPostVariable( 'OptionIDList' ) )
{
    foreach( $http->postVariable( 'OptionIDList' ) as $optionID )
    {
        $multiValue->removeOptionFromField( $field, $optionID );
    }
}

$warningList = false;

// Handle http post input
if ( $storeAction )
{
    $warningList = $multiValue->storeField( $http, $field );
    $multiValueDataType->storeClassAttribute( $contentClassAttribute, $version );
}

// Handle single select evaluate
if ( $http->hasPostVariable( 'Evaluate' ) )
{
    $multiValue->evaluateXSL( $field );
    $multiValueDataType->storeClassAttribute( $contentClassAttribute, $version );
}

if ( $http->hasPostVariable( 'EvaluateCSV' ) )
{
    $multiValue->evaluateCSV( $field );
    $multiValueDataType->storeClassAttribute( $contentClassAttribute, $version );
}

if ( $http->hasPostVariable( 'ExportCSV' ) )
{
    $multiValue->exportCSV( $field );
}

if ( ( $http->hasPostVariable( 'StoreButton' ) && !$warningList ) ||
     $http->hasPostVariable( 'CancelButton' ) )
{
    // If custom redirect is set, use it.
    if ( $http->hasSessionVariable( eZMultiValue::CUSTOM_REDIRECT ) )
    {
        $redirectURL = $http->sessionVariable( eZMultiValue::CUSTOM_REDIRECT );
        $http->removeSessionVariable( eZMultiValue::CUSTOM_REDIRECT );
        return $Module->redirectTo( $redirectURL );
    }
    return $Module->redirect( 'class', 'edit', array( $contentClassAttribute->attribute( 'contentclass_id' ),
                                                      '(language)',
                                                      $language ) );
}


$tpl = templateInit();

$tpl->setVariable( 'classAttribute', $contentClassAttribute );
$tpl->setVariable( 'field', new eZTemplateDOMElement( new eZTemplateDOMDocument( $contentClassAttribute->attribute( 'content' )->getDOMDocument() ), $field ) );
$tpl->setVariable( 'language', $language );
$tpl->setVariable( 'editTemplate', $multiValue->getClassEditTemplate( $field->getAttribute( 'type' ) ) );
$tpl->setVariable( 'fieldTypeNameMap', eZMultiValue::fieldTypeNameMap() );
$tpl->setVariable( 'version', $version );

$Result = array();
$Result['content'] = $tpl->fetch( "design:ezmultivalue/edit_class_field.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'ezmultivalue', 'eZMultiValue' ) ),
                         array( 'url' => false,
                                'text' => ezi18n( 'ezmultivalue', 'Edit class field' ) ) );

?>
