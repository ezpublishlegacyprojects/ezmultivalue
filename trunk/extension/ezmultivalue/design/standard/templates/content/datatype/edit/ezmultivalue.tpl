{default $attribute_base='ContentObjectAttribute'
         $fieldset_end=-1}


{def $fieldElementValue=''
     $value=''}

{foreach $attribute.content.definition.Field as $idx => $fieldElement}

{set $value=$attribute.content.data[$fieldElement['@field_id']]}

{switch match=$fieldElement['@type']}

{* Int, float and text line *}
{case in=array( 1,2,3 )}
<div class="block">
<label>{$fieldElement['@name']|wash}</label>
</div>

<input type="text" name="{$attribute_base}_{$fieldElement['@field_id']}"
       value="{if $value.value}{$value.value}{/if}" />
{/case}

{* Text area *}
{case match=4}
<div class="block">
<label>{$fieldElement['@name']|wash}</label>
</div>
<textarea name="{$attribute_base}_{$fieldElement['@field_id']}">{if $fieldElementValue}{$value.value|wash}{/if}</textarea>
{/case}

{* Fieldset *}
{case match=5}
<fieldset>
<legend>{$fieldElement['@name']|wash}</legend>
{set $fieldset_end=sum( $idx, $fieldElement.Options.0['@span'] )}
{/case}

{* single select *}
{case match=6}
<script>
	ezmvOptionList_{$fieldElement['@field_id']} = new Array;
</script>
    <div class="block">
        <label>{$fieldElement['@name']|wash}</label>
        {* Switch single select type *}
        {switch match=$fieldElement.Options.0['@type']}

        {case match='radio'}
            {foreach $fieldElement.xpath["Option[@depth='1']"] as $option}
                <input type="radio" name="{$attribute_base}_{$fieldElement['@field_id']}" {if $value.value|eq( $option['@value'] )}checked="checked"{/if} value="{$option['@value']}">{$option['@name']}</input>
            {delimiter}<br />{/delimiter}
            {/foreach}
        {/case}

        {case match='dropdown'}
            {def $more_deps = true()
            	 $option_list = array()
            	 $depth = 2
            	 $dep_list = array()
            }
            
            <select name="{$attribute_base}_{$fieldElement['@field_id']}" onchange="if( ezmvOptionList_{$fieldElement['@field_id']}[{$depth}] ){ldelim}ezmvOptionList_{$fieldElement['@field_id']}[{$depth}].populate();{rdelim}">
            {foreach $fieldElement.xpath["Option[@depth='1']"] as $option}
                <option value="{$option['@value']}" {if $value.value|eq( $option['@value'] )}selected="selected"{/if}>{$option['@name']}</option>
            {/foreach}
            </select>            

            {do}
            	{set $option_list = $fieldElement.xpath[concat("Option[@depth='", $depth, "']")]}
						
            	{if $option_list|count|eq(0)}
            		{set $more_deps = false()}            		
            	{else}
					<script>
					{if $depth|eq( 2 )}
						ezmvOptionList_{$fieldElement['@field_id']}[{$depth}] = new DynamicOptionList("{$attribute_base}_{$fieldElement['@field_id']}_{$depth}", "{$attribute_base}_{$fieldElement['@field_id']}");
					{else}
						ezmvOptionList_{$fieldElement['@field_id']}[{$depth}] = new DynamicOptionList("{$attribute_base}_{$fieldElement['@field_id']}_{$depth}", "{$attribute_base}_{$fieldElement['@field_id']}_{$depth|dec}");
					{/if}
					{foreach $fieldElement.xpath[concat("Option[@depth='", $depth|dec, "']")] as $prev_option}
						{set $dep_list = $fieldElement.xpath[concat("Option[@parent='", $prev_option['@Id'], "']")]}
						{if $dep_list|count}
							ezmvOptionList_{$fieldElement['@field_id']}[{$depth}].addOptions("{$prev_option['@value']}", {foreach $dep_list as $dep_option}"{$dep_option['@name']}", "{$dep_option['@value']}"{delimiter}, {/delimiter}{/foreach});
							ezmvOptionList_{$fieldElement['@field_id']}[{$depth}].setDefaultOption("{$prev_option['@value']}", "{$dep_list.0['@value']}");
						{/if}
					{/foreach}
					
					//ezmvOptionList_list{$depth}.init( document.getElementById('editform') );
					
					</script>
		            <select name="{$attribute_base}_{$fieldElement['@field_id']}_{$depth}">
		            {foreach $option_list as $option}
		                <option value="{$option['@value']}" {if $value.value|eq( $option['@value'] )}selected="selected"{/if}>{$option['@name']}</option>
		            {/foreach}
		            </select>
		            
            	{/if}
            	
            	{set $depth = $depth|inc}
            {/do while $more_deps}
            
            {literal}
            <script>
            	function initOptionLists_{/literal}{$fieldElement['@field_id']}{literal}()
            	{
            		for( var i=0; i<ezmvOptionList_{/literal}{$fieldElement['@field_id']}{literal}.length; i++ )
            		{
            			if( ezmvOptionList_{/literal}{$fieldElement['@field_id']}{literal}[i] )
            			{
            				ezmvOptionList_{/literal}{$fieldElement['@field_id']}{literal}[i].init( document.getElementById('editform') );
            				ezmvOptionList_{/literal}{$fieldElement['@field_id']}{literal}[i].populate();
            			}
            		}
            	}
            	
            	initOptionLists_{/literal}{$fieldElement['@field_id']}{literal}();
            </script>
            {/literal}
        {/case}

        {/switch}
    </div>
{/case}

{* multi select *}
{case match=7}
    {def $valueList=array()}
    {foreach $value.list as $valueItem}
       {set $valueList=$valueList|append( $valueItem.value )}
    {/foreach}
    <div class="block">
        <label>{$fieldElement['@name']|wash}</label>
        {* Switch multi select type *}
        {switch match=$fieldElement.Options.0['@type']}

        {case match='list'}
            <select name="{$attribute_base}_{$fieldElement['@field_id']}[]" multiple="multiple" size="{$fieldElement.Options.0['@rows']}" >
            {foreach $fieldElement.xpath["Option[@depth='1']"] as $option}
                <option value="{$option['@value']}" {if $valueList|contains($option['@value'])}selected="selected"{/if} >{$option['@name']}</option>
            {/foreach}
            </select>
        {/case}

        {case match='checkbox'}
            {foreach $fieldElement.xpath["Option[@depth='1']"] as $option}
                <input type="checkbox" name="{$attribute_base}_{$fieldElement['@field_id']}[]" {if $valueList|contains($option['@value'])}checked="checked"{/if} value="{$option['@value']}">{$option['@name']}
                {delimiter}<br />{/delimiter}
            {/foreach}
            </select>
        {/case}

        {/switch}
    </div>
{/case}

{* Boolean *}
{case match=8}
<div class="block">
<label>{$fieldElement['@name']|wash}</label>
<input type="checkbox" name="{$attribute_base}_{$fieldElement['@field_id']}" {if $value.value}checked="checked"{/if} />
</div>
{/case}

{/switch}

{* End fieldset *}
{if $fieldset_end|eq($idx)}
</fieldset>
{/if}

{/foreach}
