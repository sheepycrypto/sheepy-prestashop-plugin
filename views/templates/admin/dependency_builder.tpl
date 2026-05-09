{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<script src="https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js"></script>

<div id="cdc-container"></div>

<script defer>
    const renderMboCdcDependencyResolver = window.mboCdcDependencyResolver.render
    const context = {
        ...{$dependencies|json_encode},
        onDependenciesResolved: () => location.reload(),
        onDependencyResolved: (dependencyData) => console.log('Dependency installed', dependencyData),
        onDependencyFailed: (dependencyData) => console.log('Failed to install dependency', dependencyData),
        onDependenciesFailed: () => console.log('There are some errors'),
    }
    renderMboCdcDependencyResolver(context, '#cdc-container')
</script>
