const mgIntegrationHelper = {
    //help determine what part of app should be integrated
    getIngegrationInsertType: function (isIntegration) {
        var allowedInsertTypes = ['full', 'content', 'mc_content'];
        var intParam = $(isIntegration).attr('mg-integration-insert-type');
        if (allowedInsertTypes.includes(intParam))
        {
            return intParam;
        } else
        {
            return 'full';
        }
    },
    //get integration HTML code to be passed on the page
    getIngegrationCode: function (selector, integrationInsertType) {
        if (integrationInsertType === 'full')
        {
            return $(selector).parents('.mg-integration-container').children('#layers2').first()[0].outerHTML;
        } else if (integrationInsertType === 'mc_content')
        {
            return $(selector)[0].innerHTML;
        } else
        {
            return $(selector)[0].outerHTML;
        }
    },
    removeOldContainer: function (selector, integrationInsertType, integrationTarget) {
        return $(selector)[0].remove();
    },
    afterInsertActions: function (selector, integrationInsertType, integrationTarget) {
        if (integrationInsertType === 'full')
        {
            var modalContId = $('#' + $(selector).attr('id') + '_modal');
            $(integrationTarget).find(modalContId).first().remove();
        }
    }
}

mgEventHandler.on('AppsPreLoad', null, function (id, params) {
    if (typeof params.appContainers !== 'undefined')
    {
        //check all app container, looking for integration containers
        for (var key in params.appContainers)
        {
            if (!params.appContainers.hasOwnProperty(key))
            {
                continue;
            }
            
            //each integration container needs to have 'mg-integration-container' class
            var isIntegration = $(params.appContainers[key]).parents('.mg-integration-container');
            
            if (isIntegration.length === 1)
            {
                var integrationInsertType = mgIntegrationHelper.getIngegrationInsertType(isIntegration);
                var tempIntCode = mgIntegrationHelper.getIngegrationCode(params.appContainers[key], integrationInsertType);
                var integrationType = $(isIntegration).attr('mg-integration-type');
                var integrationTarget = $(isIntegration).attr('mg-integration-target');
                var relatedFieldSelector = $(isIntegration).attr('mg-integration-related-field-selector');
                var relatedFieldValues = $(isIntegration).attr('mg-integration-related-field-values').split(',');

                if (integrationTarget && integrationTarget !== 'null')
                {
                    var integrationTargetToDelete = $(isIntegration).attr('mg-integration-target');
                    integrationTarget = integrationTarget.split(',').find((selector) => $(selector).length > 0);
        
                    if (typeof integrationTarget === 'undefined')
                    {
                        $("[mg-integration-target='" + integrationTargetToDelete +"']").remove();
                        continue;
                    }
                }
                
                if (integrationType === 'append')
                {
                    mgIntegrationHelper.removeOldContainer(params.appContainers[key], integrationInsertType, integrationTarget);
                    $(integrationTarget).first().append(tempIntCode);
                    mgIntegrationHelper.afterInsertActions(params.appContainers[key], integrationInsertType, integrationTarget);
                } else if (integrationType === 'replace')
                {
                    mgIntegrationHelper.removeOldContainer(params.appContainers[key], integrationInsertType, integrationTarget);
                    $(integrationTarget).first().replaceWith(tempIntCode);
                    mgIntegrationHelper.afterInsertActions(params.appContainers[key], integrationInsertType, integrationTarget);
                } else if (integrationType === 'after')
                {
                    mgIntegrationHelper.removeOldContainer(params.appContainers[key], integrationInsertType, integrationTarget);
                    $(integrationTarget).first().after(tempIntCode);
                    mgIntegrationHelper.afterInsertActions(params.appContainers[key], integrationInsertType, integrationTarget);
                } else if (integrationType === 'before')
                {
                    mgIntegrationHelper.removeOldContainer(params.appContainers[key], integrationInsertType, integrationTarget);
                    $(integrationTarget).first().before(tempIntCode);
                    mgIntegrationHelper.afterInsertActions(params.appContainers[key], integrationInsertType, integrationTarget);
                } else if (integrationType === 'prepend')
                {
                    mgIntegrationHelper.removeOldContainer(params.appContainers[key], integrationInsertType, integrationTarget);
                    $(integrationTarget).first().prepend(tempIntCode);
                    mgIntegrationHelper.afterInsertActions(params.appContainers[key], integrationInsertType, integrationTarget);
                } else if (integrationType === 'custom')
                {
                    var contId = $(params.appContainers[key]).attr('id');
                    var integrationFunction = $(isIntegration).attr('mg-integration-function');
                    if (integrationFunction && typeof window[integrationFunction] === "function")
                    {
                        window[integrationFunction](integrationTarget, contId);
                    }
                    if (integrationTarget !== 'null')
                    {
                        $(params.appContainers[key])[0].remove();
                        $(integrationTarget).addClass('modulesgarden-app-main-container');
                        if (typeof $(integrationTarget).attr('id') === 'undefined')
                        {
                            $(integrationTarget).attr('id', contId);
                        }
                    }
                }

                if (relatedFieldSelector)
                {
                    var relatedField = $(relatedFieldSelector.split(',').find((selector) => $(selector).length > 0));

                    if (relatedField.length > 0)
                    {
                        relatedField.on("change", function() {
                            var selectedValue = this.value;
                            var foundValue = relatedFieldValues.find(value => value === selectedValue);

                            var integrationContainer = $("#" + $(params.appContainers[key]).attr('id'));

                            if (integrationContainer)
                            {
                                if (foundValue) {
                                    integrationContainer.show();
                                } else {
                                    integrationContainer.hide();
                                }
                            }
                        });
                    }
                }
            }
        }
    }
}, 3000);
