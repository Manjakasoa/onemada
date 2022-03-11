<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="panel">
            <h3>
                <i class="icon-list-ul"></i> 
                {l s='Etages' mod='cahriblock'}
                <span class="panel-heading-action">
                    <a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')}&configure=blockseo&action=add_block">
                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Créer un etage personnalisé' mod='blockseo'}" data-html="true">
                            <i class="process-icon-new "></i>
                        </span>
                    </a>
                </span>
            </h3>
            <div id="blocksContent">
                <div id="blocks">
                <div id="blocks_homepage" class="panel">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="pull-left">{l s='Homepage' mod='blockseo'}</h4>
                  
                                    <div class="btn-group-action pull-right">
                                        <a class="btn btn-default"
                                            href="{$link->getAdminLink('AdminModules')}&configure=blockseo&action=edit_homepageseoblock">
                                            <i class="icon-edit"></i>
                                            {l s='Modifier' mod='blockseo'}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {foreach from=$blocks item=block}
                        <div id="blocks_{$block.id_block}" class="panel">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="pull-left">{l s='Catégorie' mod='blockseo'} {$block.category_name}</h4>
                                      
                                    <div class="btn-group-action pull-right">
                                        <a class="btn btn-default"
                                            href="{$link->getAdminLink('AdminModules')}&configure=blockseo&action=edit_block&block={$block.id_block}">
                                            <i class="icon-edit"></i>
                                            {l s='Modifier' mod='blockseo'}
                                        </a>
                                        <a class="btn btn-default"
                                            href="{$link->getAdminLink('AdminModules')}&configure=blockseo&action=delete_block&block={$block.id_block}"
                                            {literal}onclick="if (confirm('Supprimer cet etage?\n\n')){return true;}else{event.stopPropagation(); event.preventDefault();};"{/literal}
                                            >
                                            <i class="icon-trash"></i>
                                            {l s='Supprimer' mod='blockseo'}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
