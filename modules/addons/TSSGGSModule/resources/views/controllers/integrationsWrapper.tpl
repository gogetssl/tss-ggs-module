{foreach from=$integrations key=varible item=value}
    <div class="mg-integration-container"
         mg-integration-target="{$value.integrationDetails->getPlacementSelector()}"
         mg-integration-type="{$value.integrationDetails->getType()}"
         mg-integration-function="{$value.integrationDetails->getJsFunctionName()}"
         mg-integration-insert-type="{$value.integrationDetails->getInsertType()}"
         mg-integration-related-field-selector="{$value.integrationDetails->getRelatedFieldSelector()}"
         mg-integration-related-field-values="{$value.integrationDetails->getRelatedFieldValues()}">
        {$value.htmlData}
    </div>
{/foreach}


