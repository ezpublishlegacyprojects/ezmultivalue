<fieldset>
<legend>{'Default value'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Default"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<textarea name="default">{$field.Options.0['@default']}</textarea>
</div>

</fieldset>

<fieldset>
<legend>{'Text field options'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Enabled"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="checkbox" name="enabled" {cond( $field.Options.0['@enabled'], 'checked="checked"', '')}" />
</div>

<div class="block">
<label>{"Min number of characters"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="min" value="{$field.Options.0['@min']}" />
</div>

<div class="block">
<label>{"Max number of characters"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="max" value="{$field.Options.0['@max']}" />
</div>

<div class="block">
<label>{"#Rows"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<select name="rows">
{foreach array( 2, 5, 10, 15, 20 ) as $rows}
    <option value="{$rows}" {if $field.Options.0['@rows']|eq($rows)}selected="selected"{/if}>{$rows}</option>
{/foreach}
</select>
</div>

</fieldset>