<form action={concat( 'ezmultivalue/edit_class_field/', $classAttribute.id, '/', $version, '/', $language, '/', $field['@field_id'] )|ezurl} method="post" enctype="multipart/form-data" name="eZMultiValue">

<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h2 class="context-title">{'Edit %classAttributeName% - %multiValueType%'|i18n( 'design/admin/rss/edit_import',, hash( '%classAttributeName%', $classAttribute.name, '%multiValueType%', $fieldTypeNameMap[$field['@type']] ) )|wash}</h2>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">


<div class="context-attributes">

<fieldset>
<legend>{'General options'|i18n( 'ezmultivalue/edit_class_field' )}</legend>

<div class="block">
<label>{"Name"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input id="field1" type="text" name="name" value="{$field['@name']|wash}" />
</div>

<div class="block">
<label>{"Identifier"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
<input type="text" name="identifier" value="{$field['@identifier']|wash}" />
</div>

<div class="block">
<label>{"Required"|i18n( 'ezmultivalue/edit_class_field' )}:</label>
    <input type="checkbox" name="required" {cond( $field['@required'], 'checked="checked"', '')}" />
</div>
</fieldset>

{include uri=$editTemplate}

</div>

{* DESIGN: Content END *}</div></div></div>


    {* Buttons. *}
    <div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
    <div class="block">
    <input class="button" type="submit" name="StoreButton" value="{'OK'|i18n( 'ezmultivalue/edit_class_field' )}" title="{'Apply the changes and return to class edit.'|i18n('design/admin/rss/edit_import')}" />
    <input class="button" type="submit" name="CancelButton" value="{'Cancel'|i18n( 'ezmultivalue/edit_class_field' )}" title="{'Cancel the changes and return to class edit.'|i18n('design/admin/rss/edit_import')}" />
    </div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
    </div>


</div>
</form>

{literal}
<script language="JavaScript" type="text/javascript">
<!--
    window.onload=function()
    {
        document.getElementById('field1').select();
        document.getElementById('field1').focus();
    }
-->
</script>
{/literal}
