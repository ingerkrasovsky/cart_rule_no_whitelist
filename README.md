Cart Rule No Whitelist

Description

A Prestashop module that prevents the automatic addition of new cart rules to the whitelist of other rules.

By default, in Prestashop, if an existing cart rule has the cart_rule_restriction flag enabled, all new cart rules are automatically added to its whitelist. This module removes this behavior, preventing unwanted modifications to the relationships between cart rules.

Installation

Copy the module folder to modules/cart_rule_no_whitelist/

Go to the Prestashop admin panel: Modules -> Module Manager

Find "Cart Rule No Whitelist" and install the module

Usage

After installation, the module automatically blocks the forced addition of new cart rules to the whitelist of other rules, even if they are created via the CartRule object.