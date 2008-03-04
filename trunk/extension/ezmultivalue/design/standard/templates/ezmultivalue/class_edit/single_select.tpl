<fieldset>
<legend>{'Options'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Feed URL 1"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="feed_url_1" value="{$field.Options.0['@feed_url_1']}" size="80"/>
<label>{'XSL transform'|i18n( 'ezmultivalue/edit_class_field' )}</label>
<input type="hidden" name="MAX_FILE_SIZE" value="400000"/>
{$field.Options.0['@filename']|wash}:&nbsp;<input name="xsl" type="file" />
<span class="left"><input type="submit" class="button" name="Evaluate" value="{'Evaluate'|i18n( 'extension/ezmultivalue/design/class/datatype' )}" /></span>
</div>

<div class="block">
<label>{"CSV import"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="hidden" name="MAX_FILE_SIZE" value="400000"/>
{$field.Options.0['@csv_filename']|wash}:&nbsp;<input name="csv" type="file" />
<span class="left"><input type="submit" class="button" name="EvaluateCSV" value="{'Evaluate'|i18n( 'extension/ezmultivalue/design/class/datatype' )}" />
    <input type="submit" class="button" name="ExportCSV" value="{'Export CSV'|i18n( 'extension/ezmultivalue/design/class/datatype' )}" /></span>
</div>

<table class="list" cellspacing="0">
<tr>
    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage}
    alt="{'Invert selection.'|i18n( 'extension/ezmultivalue/design/class/datatype' )}"
    title="{'Invert selection.'|i18n( 'extension/ezmultivalue/design/class/datatype' )}"
    onclick="ezjs_toggleCheckboxes( document.children, 'eZMultiValueType_IDArray_{$class_attribute.id}[]' ); return false;" /></th>
    <th>{'Name'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th>{'Value'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
    <th>{'Parent'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</th>
</tr>
{foreach $field.Option as $option}
<tr>
    <td><input type="checkbox" name="OptionIDList[]" value="{$option['@Id']}" /></td>
    <td><input type="text" name="name_{$option['@Id']}" value="{$option['@name']|wash}" size="40"/></td>
    <td><input type="text" name="value_{$option['@Id']}" value="{$option['@value']|wash}" size="40"/></td>
    <td><select name="parent_{$option['@Id']}">
    <option value="0">{'None'|i18n( 'extension/ezmultivalue/design/class/datatype' )}</option>
    {foreach $field.Option as $parentOption}
        {if $option['@Id']|eq($parentOption['@Id'])|not}
            <option value="{$parentOption['@Id']}" {cond( $option['@parent']|eq( $parentOption['@Id'] ), 'selected="selected"', '' )} >{$parentOption['@name']|wash}</option>
        {/if}
    {/foreach}
    </select></td>
</tr>
{/foreach}
</table>
<div class="block">
<span class="left"><input type="submit" class="button" name="RemoveOptions" value="{'Remove'|i18n( 'extension/ezmultivalue/design/class/datatype' )}" /></span>
<span class="right"><input type="submit" class="button" name="AddOption" value="{'Add'|i18n( 'extension/ezmultivalue/design/class/datatype' )}" /></span>
</div>

<div class="block">
<label>{"Selection type"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<label><input type="radio" name="type" value="radio" {cond( eq( $field.Options.0['@type'], 'dropdown' )|not, 'checked="checked"', '' )} />{'Radio'|i18n( 'ezmultivalue/edit_class_field' )}</label>
<label><input type="radio" name="type" value="dropdown" {cond( eq( $field.Options.0['@type'], 'radio' )|not, 'checked="checked"', '' )} />{'Drop-down'|i18n( 'ezmultivalue/edit_class_field' )}</label>
</div>


</fieldset>