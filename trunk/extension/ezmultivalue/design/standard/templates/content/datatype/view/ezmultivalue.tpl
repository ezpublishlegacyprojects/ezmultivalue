{default $fieldset_end=-1}


{foreach $attribute.content.data as $idx => $value}

{switch match=$value.type}

{case in=array( 1,2,3,4 )}
<div class="block">
<label>{$value.name|wash}</label>
{$value.value|wash}
</div>
{/case}

{case match=5}
<fieldset>
<legend>{$value.name|wash}</legend>
{set $fieldset_end=sum( $idx, $value.field.Options.0['@span'] )}
{/case}

{case match=6}
<div class="block">
<label>{$value.name|wash}</label>
{$value.value|wash}
</div>
{/case}

{case match=7}
<div class="block">
<label>{$value.name|wash}</label>
{foreach $value.list as $valueItem}
    {$valueItem.value|wash}
    {delimiter}<br />{/delimiter}
{/foreach}
</div>
{/case}

{case match=8}
<div class="block">
<label>{$value.name|wash}</label>
{cond( $value.value, 'Enabled'|i18n( 'extension/ezmultivalue/design' ), 'Disabled'|i18n( 'extension/ezmultivalue/design' ) )}
</div>
{/case}

{/switch}

{* End fieldset *}
{if $fieldset_end|eq($idx)}
</fieldset>
{/if}

{/foreach}
