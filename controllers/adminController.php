<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractAdminController.php';

class AdminController extends AbstractAdminController {

    public function actionCSV($attributes) {
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();

        $content = ContentFactory::search()
                ->select();

        $sampleContent = ContentFactory::buildNewModel();
        $uiTableCols = $sampleContent->getUITableCols();

        $csv = new GI_CSV('asdf/test');
        $csv->setOverWrite(true);
        $csv->setUITableCols($uiTableCols, true);
        $csv->addModelRows($content);

        $csvFile = $csv->getCSVFilePath();
        $view->echoThis('<h2>' . $csvFile . '</h2>');
        $view->echoThis(file_get_contents($csvFile));
        $view->echoThis('<a href="' . $csvFile . '" target="_blank" >Download</a>');
        return GI_Controller::getReturnArray($view);
    }

    public function actionRepairFolders($attributes) {
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();
        $foldersWithNoRefs = FolderFactory::search()
                ->filterNull('ref')
                ->select();

        if (empty($foldersWithNoRefs)) {
            $view->echoThis('--None--');
        } else {
            foreach ($foldersWithNoRefs as $folder) {
                $needSave = false;
                if ($folder->getProperty('title') == 'my files') {
                    $folder->setProperty('system', 1);
                    $needSave = true;
                }
                if ($folder->getProperty('system')) {
                    $needSave = true;
                }
                if ($needSave) {
                    $view->varDumpThis($folder->save());
                    $view->echoThis('--Done--');
                    $view->echoThis('');
                } else {
                    $view->echoThis('--Already Done--');
                    $view->echoThis('');
                }
            }
        }

        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionDateTime($attributes) {
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();
        $userDateTime = '1990-10-15 9:00 PM';
        $userDate = '1990-10-15';
        $gmtDateTime = '1990-10-16 4:00 AM';
        $gmtDate = '1990-10-16';
        $view->echoThis('--formatToGMT--')
                ->tab()->echoThis('<i>' . $userDateTime . '</i>')
                ->tab()->tab()->echoThis('datetime - ' . GI_Time::formatToGMT($userDateTime))
                ->tab()->tab()->echoThis('date - ' . GI_Time::formatToGMT($userDateTime, 'date'))
                ->tab()->echoThis('<i>' . $userDate . '</i>')
                ->tab()->tab()->echoThis('date - ' . GI_Time::formatToGMT($userDate, 'date'));
        $view->echoThis('--UPDATED--');
        $view->echoThis();

        $view->echoThis('--formatToUserTime--')
                ->tab()->echoThis('<i>' . $gmtDateTime . '</i>')
                ->tab()->tab()->echoThis('datetime - ' . GI_Time::formatToUserTime($gmtDateTime))
                ->tab()->tab()->echoThis('date - ' . GI_Time::formatToUserTime($gmtDateTime, 'date'))
                ->tab()->echoThis('<i>' . $gmtDate . '</i>')
                ->tab()->tab()->echoThis('date - ' . GI_Time::formatToUserTime($gmtDate, 'date'));
        $view->echoThis('--UPDATED--');
        $view->echoThis();

        $view->echoThis('--formatTimeSince--')
                ->tab()->echoThis('<i>' . $userDateTime . '</i>')
                ->tab()->tab()->echoThis(GI_Time::formatTimeSince($userDateTime, NULL, 2, true));
        $view->echoThis('--UPDATED--');
        $view->echoThis();

        $view->echoThis('--formatTimeUntil--')
                ->tab()->echoThis('<i>' . $userDateTime . '</i>')
                ->tab()->tab()->echoThis(GI_Time::formatTimeUntil('now', $userDateTime, 2, true));
        $view->echoThis('--UPDATED--');
        $view->echoThis();

        $view->echoThis('--formatFromDateToDate--')
                ->tab()->echoThis('<i>' . $userDateTime . '</i>')
                ->tab()->tab()->echoThis(GI_Time::formatFromDateToDate($userDateTime, 'now'));
        $view->echoThis();

        return GI_Controller::getReturnArray($view);
    }
    
    public function actionSetDefaultPricingUnits($attributes){
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();
        
        $defaultPricingUnits = array(
            'unit',
            'hour',
            'km',
            'pair',
            'roll',
            'can',
            'set',
            'day',
            'pc',
            'pkg',
            'bag',
            'box',
            'case',
            'gal',
            'qt',
            'pt',
            'oz',
            'floz',
            'ml',
            'l',
            'lb',
            'inch',
            'ft',
            'cm',
            'm',
            'sqft',
        );
        
        $pricingUnits = PricingUnitFactory::search()
                ->select();
        
        foreach($pricingUnits as $pricingUnit){
            /* @var $pricingUnit AbstractPricingUnit */
            $longRef = $pricingUnit->getProperty('long_ref');
            if(in_array($longRef, $defaultPricingUnits)){
                $pricingUnit->setProperty('active', 1);
                $view->echoThis('<span class="green">' . $pricingUnit->getTitle() . ' Marked Active</span>');
            } else {
                $pricingUnit->setProperty('active', 0);
                $view->echoThis($pricingUnit->getTitle() . ' Marked Inactive');
            }
            $pricingUnit->save();
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionBatchPriceSheetItems($attributes){
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();
        $view->setTitle('Batch Price Sheet Items');
        $view->addSiteTitle('Batch Price Sheet Items');
        
        $form = new GI_Form('batch_form');
        
        $form->addField('batch', 'textarea', array(
            'displayName' => 'Items',
            'required' => true
        ));
        
        $form->addHTML('<span class="submit_btn">Submit</span>');
        
        if($form->wasSubmitted() && $form->validate()){
            $batch = filter_input(INPUT_POST, 'batch');
            $rows = explode(PHP_EOL, $batch);
            $priceSheets = PriceSheetFactory::search()
                    ->select();
            if(!$priceSheets || count($priceSheets) > 1){
                $view->echoThis('<span class="red">No price sheets found.</span>');
            } else {
                $priceSheet = $priceSheets[0];
                foreach($rows as $row){
                    $itemData = preg_split("/[\t]/", $row);
                    
                    if(empty($itemData) || (isset($itemData[0]) && empty($itemData[0]))){
                        continue;
                    }
                    $title = trim($itemData[0]);
                    $typeRef = trim($itemData[1]);
                    $pricePerUnit = (float) str_replace('$', '', $itemData[2]);
                    $longRef = trim($itemData[3]);
                    
                    $existingItem = PriceSheetItemFactory::getModelByTitle($title);
                    if($existingItem){
                        $view->echoThis('<span><b>' . $title . '</b> already added.</span>');
                        continue;
                    }
                    $newItem = PriceSheetItemFactory::buildNewModel($typeRef);
                    if(!$newItem){
                        $view->echoThis('<span class="red"><b>' . $title . '</b> could not be added.</span>');
                        continue;
                    }
                    $newItem->setProperty('title', $title);
                    $newItem->setProperty('cost_per_unit', $pricePerUnit);
                    $unit = PricingUnitFactory::getModelByLongRef($longRef);
                    if(!$unit){
                        $view->echoThis('<span class="red"><b>' . $title . '</b> could not be added. [unit "' . $longRef . '" not found]</span>');
                        continue;
                    }
                    $newItem->setProperty('pricing_unit_id', $unit->getId());
                    $defaultRef =  ProjectConfig::getDefaultCurrencyRef();
                    $defaultCur = CurrencyFactory::getModelByRef($defaultRef);
                    if(!$defaultCur){
                        $view->echoThis('<span class="red"><b>' . $title . '</b> could not be added. [currency not found]</span>');
                        continue;
                    }
                    $newItem->setProperty('currency_id', $defaultCur->getId());
                    if(!$newItem->save()){
                        $view->echoThis('<span class="red"><b>' . $title . '</b> could not be saved.</span>');
                        continue;
                    }
                    $link = PriceSheetHasItemFactory::getByItemAndPriceSheet($newItem, $priceSheet, true);
                    $link->setProperty('price_per_unit', $pricePerUnit);
                    if(!$link->save()){
                        return false;
                    }
                    $view->echoThis('<span class="green"><b>' . $title . '</b> saved.</span>');
                }
            }
        }
        
        $view->echoThis($form->getForm());
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'link' => GI_URLUtils::buildURL($attributes),
                'label' => 'Batch Price Sheet Items'
            )
        );
        return $returnArray;
    }
    
    public function actionSetDefaultProjectColours($attributes){
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();
        $projects = ProjectFactory::search()
                ->filter('colour', '1b98fc')
                ->select();
        if($projects){
            foreach($projects as $project){
                $projectId = $project->getId();
                $newColour = GI_Colour::getRandomColour('default', NULL, $projectId);
                $title = $project->getProjectNumber();
                if(!$project->changeColour($newColour)){
                    $view->echoThis('<span class="red">Failed changing colour of <b>' . $title . '</b>.</span>');
                } else {
                    $view->echoThis('<span class="green">Successfully changed colour of <b>' . $title . '</b>.</span>');
                }
            }
        } else {
            $view->echoThis('<span>Nothing to do.</span>');
        }

        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionRepairTimesheetLineTotals($attributes){
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new AdminEchoView();
        $timesheetLines = TimesheetLineFactory::search()
                ->filter('sortable_total', 0)
                ->select();
        if($timesheetLines){
            foreach($timesheetLines as $timesheetLine){
                $timesheetLine->calculateSortableTotal();
                if(!$timesheetLine->save()){
                    $view->echoThis('<span class="red">Failed updating total of timesheet line <b>' . $timesheetLine->getId() . '</b>.</span>');
                } else {
                    $view->echoThis('<span class="green">Successfully updated total of timesheet line <b>' . $timesheetLine->getId() . '</b>.</span>');
                }
            }
        } else {
            $view->echoThis('<span>Nothing to do.</span>');
        }

        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionOrderLineMerger($attributes){
        if(!Permission::verifyByRef('super_admin')){
            GI_URLUtils::redirectToAccessDenied();
            die();
        }
        $form = new GI_Form('order_line_merger');
        
        $form->addField('from_order_line_id', 'id', array(
            'displayName' => 'From Order Line ID',
            'required' => true
        ));
        
        $form->addField('to_order_line_id', 'id', array(
            'displayName' => 'To Order Line ID',
            'required' => true
        ));
        $fromOrderLineId = filter_input(INPUT_POST, 'from_order_line_id');
        $toOrderLineId = filter_input(INPUT_POST, 'to_order_line_id');
        
        $problem = false;
        $cause = '';
        if($form->wasSubmitted() && !empty($fromOrderLineId) && !empty($toOrderLineId)){
            $fromOrderLine = OrderLineFactory::getModelById($fromOrderLineId);
            $toOrderLine = OrderLineFactory::getModelById($toOrderLineId);
            
            if($fromOrderLine && $toOrderLine){
                if($fromOrderLine->getTypeRef() == 'purchase' || $fromOrderLine->getTypeRef() == 'purchase'){
                    $problem = true;
                    $cause = 'Cannot currently merge purchase order lines';
                } elseif($fromOrderLine->getProperty('order_id') != $toOrderLine->getProperty('order_id')){
                    $problem = true;
                    $cause = 'Different Orders (' . $fromOrderLine->getOrderNumber() . ' and ' . $toOrderLine->getOrderNumber() . ')';
                } elseif($fromOrderLine->getProperty('order_line_sales.inv_pack_config_id') != $toOrderLine->getProperty('order_line_sales.inv_pack_config_id')){
                    $cause = 'Different Item Packages (' . $fromOrderLine->getItemTitle() . ' and ' . $toOrderLine->getItemTitle() . ')';
                } else {
                    $string = 'Merge ';
                    $string .= '<u>' . $fromOrderLine->getQty() . '</u>';
                    $string .= ' ';
                    $string .= '<u>' . $fromOrderLine->getItemTitle() . '</u>';
                    $string .= ' into ';
                    $string .= '<u>' . $toOrderLine->getQty() . '</u>';
                    $string .= ' ';
                    $string .= '<u>' . $toOrderLine->getItemTitle() . '</u>';
                    $form->addHTML('<p>' . $string . '</p>');
                }
            } else {
                $problem = true;
                $cause = 'Could not find order line';
            }
            $form->addField('confirm', 'onoff', array(
                'displayName' => 'Confirm',
                'required' => true,
                'clearValue' => true
            ));
        }
        if($form->wasSubmitted() && $form->validate() && !$problem){
            $fromOLHSResult = OrderLineHasStockFactory::search()
                    ->filter('order_line_id', $fromOrderLineId)
                    ->select();
            foreach($fromOLHSResult as $fromOLHS){
                $fromOLHS->setProperty('order_line_id', $toOrderLineId);
                if(!$fromOLHS->save()){
                    $problem = true;
                    $cause .= 'problem during order line has stock merging<br/>';
                }
            }
            
            $expenseSearch = new GI_DataSearch('item_link_to_expense_item');
            $expenseLinks = $expenseSearch->filter('item_id', $fromOrderLineId)
                    ->filter('table_name', 'order_line')
                    ->select();
            foreach($expenseLinks as $expenseLink){
                $expenseLink->setProperty('item_id', $toOrderLineId);
                if(!$expenseLink->save()){
                    $problem = true;
                    $cause .= 'problem during expense merging<br/>';
                }
            }
            
            $incomeSearch = new GI_DataSearch('item_link_to_income_item');
            $incomeLinks = $incomeSearch->filter('item_id', $fromOrderLineId)
                    ->filter('table_name', 'order_line')
                    ->select();
            foreach($incomeLinks as $incomeLink){
                $incomeLink->setProperty('item_id', $toOrderLineId);
                if(!$incomeLink->save()){
                    $problem = true;
                    $cause .= 'problem during income merging<br/>';
                }
            }
            
            $contResult = InvContainerFactory::search()
                    ->filter('sales_order_line_id', $fromOrderLineId)
                    ->select();
            foreach($contResult as $cont){
                $cont->setProperty('sales_order_line_id', $toOrderLineId);
                if(!$cont->save()){
                    $problem = true;
                    $cause .= 'problem during container merging<br/>';
                }
            }
            
            $stockResult = InvStockFactory::search()
                    ->filter('sales_order_line_id', $fromOrderLineId)
                    ->select();
            foreach($stockResult as $invStock){
                $invStock->setProperty('sales_order_line_id', $toOrderLineId);
                if(!$invStock->save()){
                    $problem = true;
                    $cause .= 'problem during stock merging<br/>';
                }
            }
            
            $fromACLines = InvStockFactory::search()
                    ->filter('p_order_line_id', $fromOrderLineId)
                    ->select();
            foreach($fromACLines as $fromACLine){
                if(!$fromACLine->isEcoFee()){
                    $fromACLine->setProperty('p_order_line_id', $toOrderLineId);
                    if(!$fromACLine->save()){
                        $problem = true;
                        $cause .= 'problem during additional cost merging<br/>';
                    }
                }
            }
            
            $toOrderLineQty = $toOrderLine->getQty();
            $fromOrderLineQty = $fromOrderLine->getQty();
            $newOrderLineQty = $toOrderLineQty + $fromOrderLineQty;
            
            $toOrderLine->setProperty('subtotal', $toOrderLine->getSubtotalForQty($newOrderLineQty));
            $toOrderLine->setProperty('qty', $newOrderLineQty);
            if(!$toOrderLine->save()){
                $problem = true;
                $cause .= 'problem saving order line<br/>';
            }
            
            $acLines = $toOrderLine->getAdditionalCostLines();
            if (!empty($acLines)) {
                foreach ($acLines as $acLine) {
                    if($acLine->isEcoFee()){
                        $ecoFee = EcoFeeFactory::getModelById($acLine->getProperty('order_line_ac_sales_eco.eco_fee_id'));
                        $rate = (float) $ecoFee->getProperty('rate_per_unit');
                        $qtyConversionRate = (float) $acLine->getProperty('order_line_ac_sales_eco.conv_qty');
                        $baseUnitQty = $toOrderLine->getBaseUnitCount();
                        $convertedQty = $baseUnitQty * $qtyConversionRate;
                        $newEcoFeeSubTot = round($convertedQty * $rate, 2);
                        $acLine->setProperty('subtotal', $newEcoFeeSubTot);
                        $acLine->setProperty('qty', 1);
                        if(!$acLine->save()){
                            $problem = true;
                            $cause .= 'problem saving eco fees<br/>';
                        }
                    }
                }
            }
            
            if(!$problem && !$fromOrderLine->softDelete()){
                $problem = true;
                $cause .= 'problem deleting order line<br/>';
            }
            
            if(!$problem){
                $newURLAttrs = $attributes;
                $newURLAttrs['merged'] = 1;
                GI_URLUtils::redirect($newURLAttrs);
            }
        }
        
        if(isset($attributes['merged']) && $attributes['merged']){
            $view = new AdminEchoView();
            $view->setTitle('Merge Order Lines');
            $view->addString('<p class="green">Merged successfully</p>');
            $mergeAgainAttrs = $attributes;
            unset($mergeAgainAttrs['merged']);
            $view->addFootHTML('<br/><a href="' . GI_URLUtils::buildURL($mergeAgainAttrs) . '" title="Merge Again" class="other_btn">Merge Again</span>');
        } elseif($problem){
            $view = new AdminEchoView();
            $view->setTitle('Merge Order Lines');
            $view->addString('<p class="red">Merge failed for some reason</p>');
            $view->addString('<p>' . $cause . '</p>');
            $mergeAgainAttrs = $attributes;
            unset($mergeAgainAttrs['merged']);
            $view->addFootHTML('<br/><a href="' . GI_URLUtils::buildURL($mergeAgainAttrs) . '" title="Merge Again" class="other_btn">Try Again</span>');
        } else {
            $view = new GenericAcceptCancelFormView($form);
            $view->setHeaderText('Merge Order Lines');
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionRepairACExpenseItemLinks($attributes){
        $view = new AdminEchoView();
        
        $linkSearch = new GI_DataSearch('item_link_to_expense_item');
        $linkTable = $linkSearch->prefixTableName('item_link_to_expense_item');
        $linkSearch->innerJoin('item_link_to_expense_item', 'expense_item_id', $linkTable, 'expense_item_id', 'ILTEI')
                ->filter('ILTEI.table_name', 'inv_stock');
        $linkSearch->filter('table_name', 'inv_stock')
                ->groupBy('id')
                ->having('COUNT(' . $linkTable . '.id) > 1')
                ->setSortDescending(true);
        $links = $linkSearch->select();
        
        $skippedExpenseItemIds = array();
        $deletions = 0;
        foreach($links as $link){
            $expenseItemId = $link->getProperty('expense_item_id');
            if(in_array($expenseItemId, $skippedExpenseItemIds)){
                if($link->softDelete()){
                    $deletions++;
                }
            } else {
                $skippedExpenseItemIds[] = $expenseItemId;
            }
        }
        
        $view->echoThis('Deletions: ' . $deletions);
        
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;

    }

}
