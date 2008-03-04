<fieldset>
<legend>{'Default value'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Default"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="default" value="{$field.Options.0['@default']}" />
</div>

</fieldset>

<fieldset>
<legend>{'Min/Max values'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Enabled"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="checkbox" name="enabled" {cond( $field.Options.0['@enabled'], 'checked="checked"', '')}" />
</div>

<div class="block">
<label>{"Min"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="min" value="{$field.Options.0['@min']}" />
</div>

<div class="block">
<label>{"Max"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="max" value="{$field.Options.0['@max']}" />
</div>

</fieldset>