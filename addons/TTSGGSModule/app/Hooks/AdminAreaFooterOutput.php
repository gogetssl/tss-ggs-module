<?php

use ModulesGarden\TTSGGSModule\Core\Hook\HookIntegrator;


$hookManager->register(
    function($args) {
        $hookIntegrator = new HookIntegrator($args);

        /**
         * @var $toReturn is a HTML integration code (or null if no integration was made)
         * you can add your code to this var before returning its content,
         * do not overwrite this var!
         */
        $toReturn = $hookIntegrator->getHtmlCode();

        //===[modify product datatable]=================================================================================
        $page = basename($_SERVER['PHP_SELF']);
        if($page == "addonmodules.php" && $_GET["module"] == "TTSGGSModule" && $_GET["mg-page"] == "products")
        {
            $toReturn.= <<<SCRIPT
                <script type="text/javascript">
                    $(document).ready(function() {
                        setInterval(function() {
                            let tableBody = $('#productsDataTable').find('tbody');
                            let checkboxes = tableBody.find('.table-mass-action-check');
                            
                            checkboxes.each(
                                function(index, element)
                                {
                                    let checkbox = $(this);
                                    let row = checkbox.closest("tr");
                                    let div = checkbox.closest("div");
                                    let label = row.find('.lu-label--success');
                                    let falseDiv = row.find(".false-div");

                                    if(label.length)
                                    {
                                        div.hide();
                                        if(!falseDiv.length)
                                        {
                                            checkbox.closest('div').after(`
                                                <div class="lu-form-check false-div">
                                                    <label class="disabled">
                                                        <input type="checkbox" class="lu-form-checkbox" value="" style="display: none;" disabled="disabled">
                                                        <span class="lu-form-indicator"></span>
                                                    </label>
                                                </div>
                                            `);
                                        }
        
                                        if(checkbox.prop('checked'))
                                        {
                                            checkbox.click();
                                        }
                                    }
                                    else
                                    {
                                        div.show();
                                        falseDiv.remove();
                                    }
                                }
                            );
                        }, 100)
                    });
                </script>   
SCRIPT;
        }

        //==============================================================================================================

        if ($toReturn)
        {
            return $toReturn;
        }
    },
    100
);
