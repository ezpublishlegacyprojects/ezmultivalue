<fieldset>
<legend>{'Default value'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Default"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="checkbox" name="enabled" {cond( $field.Options.0['@default'], 'checked="checked"', '')}" />
</div>

</fieldset>