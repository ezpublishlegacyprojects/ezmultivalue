{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{let content=$class_attribute.content}


<div class="block">
<label>{'Field list'|i18n( 'extension/ezmultivalue/design/class/datatype' )}:</label>
<table class="list" cellspacing="0" width="100%">
<tr>
    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage}
    alt="{'Invert selection.'|i18n( 'extension/ezmultivalue/design/class/datatype' )}"
    title="{'Invert selection.'|i18n( 'extension/ezmultivalue/design/class/datatype' )}"
    onclick="ezjs_toggleCheckboxes( document.children, 'eZMultiValueType_IDArray_{$class_attribute.id}[]' ); return false;" /></th>
    <th>{'Type'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th>{'Name'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th>{'Identifier'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th class="tight">{'Required'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th class="tight">{'Edit'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th class="tight">{'Placement'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
</tr>
{foreach $content.definition.Field as $fieldElement}
    <tr>
        <td><input type="checkbox" name="eZMultiValueType_IDArray_{$class_attribute.id}[]" value="{$fieldElement['@field_id']}" /></td>
        <td>{$content.field_type_name_map[$fieldElement['@type']]|wash}</td>
        <td>{$fieldElement['@name']|wash}</td>
        <td>{$fieldElement['@identifier']|wash}</td>
        <td>{cond( $fieldElement['@required'], 'Yes'|i18n( 'ezmultivalue/edit_class_field' ), 'No'|i18n( 'ezmultivalue/edit_class_field' ) )}</td>
        <td><a href={concat( 'ezmultivalue/edit_class_field/', $class_attribute.id, '/', $class_attribute.version, '/nor-NO/', $fieldElement['@field_id'] )|ezurl}><img src={'edit.gif'|ezimage} alt="{'Edit'|i18n( 'ezmultivalue/edit_class_field' )}" /></a></td>

        <td><input type="text" name="eZMultiValueType_Placement_{$fieldElement['@field_id']}" value="{$fieldElement['@placement']}" size="2" /></td>
    </tr>
{/foreach}
</table>


<div class="block">
<div class="element" align="left">
    <input class="button" type="submit" name="CustomActionButton[{$class_attribute.id}_remove_selected]" value="{'Remove selection'|i18n('design/standard/class/datatype')}" />
    <select name="eZMultiValueType_FieldType_{$class_attribute.id}">
    {foreach $content.field_type_name_map as $fieldID => $fieldName}
        <option value="{$fieldID}">{$fieldName|wash}</option>
    {/foreach}
    </select>
    <input class="button" type="submit" name="CustomActionButton[{$class_attribute.id}_add_field]" value="{'Add field'|i18n('design/standard/class/datatype')}" />
    <input class="button" type="submit" name="CustomActionButton[{$class_attribute.id}_update_placement]" value="{'Update placement'|i18n('design/standard/class/datatype')}" />
</div>
</div>

</div>


{/let}
