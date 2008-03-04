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

/*! \file function_definition.php
*/

$FunctionList = array();

$FunctionList['classAttribute'] = array( 'name' => 'classAttribute',
                                         'operation_types' => 'read',
                                         'call_method' => array( 'class' => 'eZMultiValueFunctionCollection',
                                                                 'include_file' => 'extension/ezmultivalue/modules/ezmultivalue/ezmultivaluefunctioncollection.php',
                                                                 'method' => 'getClassAttribute' ),
                                          'parameter_type' => 'standard',
                                          'parameters' => array( array( 'name' => 'identifier',
                                                                        'type' => 'string',
                                                                        'required' => true,
                                                                        'default' => null ) ) );

$FunctionList['optionName'] = array( 'name' => 'optionName',
                                     'operation_types' => 'read',
                                     'call_method' => array( 'class' => 'eZMultiValueFunctionCollection',
                                                             'include_file' => 'extension/ezmultivalue/modules/ezmultivalue/ezmultivaluefunctioncollection.php',
                                                             'method' => 'getOptionName' ),
                                     'parameter_type' => 'standard',
                                     'parameters' => array( array( 'name' => 'classAttribute',
                                                                   'type' => 'object',
                                                                   'required' => true,
                                                                   'default' => null ),
                                                            array( 'name' => 'fieldIdentifier',
                                                                   'type' => 'string',
                                                                   'required' => false,
                                                                   'default' => null ),
                                                            array( 'name' => 'value',
                                                                   'type' => 'string',
                                                                   'required' => false,
                                                                   'default' => null ) ) );

$FunctionList['value'] = array( 'name' => 'value',
                                'operation_types' => 'read',
                                'call_method' => array( 'class' => 'eZMultiValueFunctionCollection',
                                                        'include_file' => 'extension/ezmultivalue/modules/ezmultivalue/ezmultivaluefunctioncollection.php',
                                                        'method' => 'value' ),
                                'parameter_type' => 'standard',
                                'parameters' => array( array( 'name' => 'data',
                                                              'type' => 'object',
                                                              'required' => true,
                                                              'default' => null ),
                                                       array( 'name' => 'field_id',
                                                              'type' => 'string',
                                                              'required' => false,
                                                              'default' => null ) ) );

$FunctionList['valueList'] = array( 'name' => 'value',
                                    'operation_types' => 'read',
                                    'call_method' => array( 'class' => 'eZMultiValueFunctionCollection',
                                                            'include_file' => 'extension/ezmultivalue/modules/ezmultivalue/ezmultivaluefunctioncollection.php',
                                                            'method' => 'valueList' ),
                                    'parameter_type' => 'standard',
                                    'parameters' => array( array( 'name' => 'data',
                                                                  'type' => 'object',
                                                                  'required' => true,
                                                                  'default' => null ),
                                                           array( 'name' => 'field_id',
                                                                  'type' => 'string',
                                                                  'required' => false,
                                                                  'default' => null ) ) );

?>
