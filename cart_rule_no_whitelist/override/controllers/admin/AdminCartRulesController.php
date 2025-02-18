<?php

class AdminCartRulesController extends AdminCartRulesControllerCore
{
    /**
     * @TODO Move this function into CartRule
     *
     * @param ObjectModel $currentObject
     *
     * @return bool|void
     *
     * @throws PrestaShopDatabaseException
     */
    protected function afterAdd($currentObject)
    {
        // Add shop restrictions if employee has not access to all shops
        $context = Context::getContext();
        $all_shops = Shop::getCompleteListOfShopsID();
        if ($context->employee->isSuperAdmin()) {
            $employee_shops = $all_shops;
        } else {
            $employee_shops = $context->employee->getAssociatedShops();
        }
        if (count($all_shops) > count($employee_shops) && Tools::getValue('shop_restriction') == '0') {
            $values = [];
            foreach ($employee_shops as $id) {
                $values[] = '(' . (int) $currentObject->id . ',' . (int) $id . ')';
            }
            Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`) VALUES ' . implode(',', $values));
        }
        // Add restrictions for generic entities like country, carrier and group
        foreach (['country', 'carrier', 'group', 'shop'] as $type) {
            if (Tools::getValue($type . '_restriction') && is_array($array = Tools::getValue($type . '_select')) && count($array)) {
                $values = [];
                foreach ($array as $id) {
                    $values[] = '(' . (int) $currentObject->id . ',' . (int) $id . ')';
                }
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_' . $type . '` (`id_cart_rule`, `id_' . $type . '`) VALUES ' . implode(',', $values));
            }
        }
        // Add cart rule restrictions
        if (Tools::getValue('cart_rule_restriction') && is_array($array = Tools::getValue('cart_rule_select')) && count($array)) {
            $values = [];
            foreach ($array as $id) {
                $values[] = '(' . (int) $currentObject->id . ',' . (int) $id . ')';
            }
            Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) VALUES ' . implode(',', $values));
        }
        // Add product rule restrictions
        if (Tools::getValue('product_restriction') && is_array($ruleGroupArray = Tools::getValue('product_rule_group')) && count($ruleGroupArray)) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
				VALUES (' . (int) $currentObject->id . ', ' . (int) Tools::getValue('product_rule_group_' . $ruleGroupId . '_quantity') . ')');
                $id_product_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('product_rule_' . $ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule` (`id_product_rule_group`, `type`)
						VALUES (' . (int) $id_product_rule_group . ', "' . pSQL(Tools::getValue('product_rule_' . $ruleGroupId . '_' . $ruleId . '_type')) . '")');
                        $id_product_rule = Db::getInstance()->Insert_ID();

                        $values = [];
                        foreach (Tools::getValue('product_rule_select_' . $ruleGroupId . '_' . $ruleId) as $id) {
                            $values[] = '(' . (int) $id_product_rule . ',' . (int) $id . ')';
                        }
                        $values = array_unique($values);
                        if (count($values)) {
                            Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES ' . implode(',', $values));
                        }
                    }
                }
            }
        }

        if (Tools::getValue('cart_rule_restriction')) {
            $ruleCombinations = Db::getInstance()->executeS('
			SELECT cr.id_cart_rule
			FROM ' . _DB_PREFIX_ . 'cart_rule cr
			WHERE cr.id_cart_rule != ' . (int) $currentObject->id . '
			AND cr.cart_rule_restriction = 0
			AND NOT EXISTS (
				SELECT 1
				FROM ' . _DB_PREFIX_ . 'cart_rule_combination
				WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_2 AND ' . (int) $currentObject->id . ' = id_cart_rule_1
			)
			AND NOT EXISTS (
				SELECT 1
				FROM ' . _DB_PREFIX_ . 'cart_rule_combination
				WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_1 AND ' . (int) $currentObject->id . ' = id_cart_rule_2
			)
			');
            foreach ($ruleCombinations as $incompatibleRule) {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET cart_rule_restriction = 1 WHERE id_cart_rule = ' . (int) $incompatibleRule['id_cart_rule'] . ' LIMIT 1');
                Db::getInstance()->execute('
				INSERT IGNORE INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
					SELECT id_cart_rule, ' . (int) $incompatibleRule['id_cart_rule'] . ' FROM `' . _DB_PREFIX_ . 'cart_rule`
					WHERE active = 1
					AND id_cart_rule != ' . (int) $currentObject->id . '
					AND id_cart_rule != ' . (int) $incompatibleRule['id_cart_rule'] . '
				)');
            }
        }
    }
}