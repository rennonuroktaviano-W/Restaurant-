<?php

namespace App\Support;

class Permissions
{
    public const MENU_VIEW = 'menu.view';

    public const CATALOG_VIEW = 'catalog.view';

    public const CATALOG_MANAGE = 'catalog.manage';

    public const ORDER_VIEW = 'order.view';

    public const ORDER_CREATE = 'order.create';

    public const ORDER_ACCEPT = 'order.accept';

    public const ORDER_CANCEL = 'order.cancel';

    public const ORDER_UPDATE_STATUS = 'order.update_status';

    public const KITCHEN_VIEW = 'kitchen.view';

    public const KITCHEN_UPDATE_STATUS = 'kitchen.update_status';

    public const PAYMENT_INITIATE = 'payment.initiate';

    public const PAYMENT_CONFIRM_CASH = 'payment.confirm_cash';

    public const PAYMENT_OVERRIDE = 'payment.override';

    public const PAYMENT_REFUND = 'payment.refund';

    public const INVENTORY_VIEW = 'inventory.view';

    public const INVENTORY_UPDATE = 'inventory.update';

    public const LOCATION_VIEW = 'location.view';

    public const LOCATION_MANAGE = 'location.manage';

    public const DISCOUNT_MANAGE = 'discount.manage';

    public const REPORT_VIEW = 'report.view';

    public const REPORT_EXPORT = 'report.export';

    public const USER_MANAGE = 'user.manage';

    public const SETTING_MANAGE = 'setting.manage';

    public const AUDIT_VIEW = 'audit.view';

    public const ALL = [
        self::MENU_VIEW,
        self::CATALOG_VIEW,
        self::CATALOG_MANAGE,
        self::ORDER_VIEW,
        self::ORDER_CREATE,
        self::ORDER_ACCEPT,
        self::ORDER_CANCEL,
        self::ORDER_UPDATE_STATUS,
        self::KITCHEN_VIEW,
        self::KITCHEN_UPDATE_STATUS,
        self::PAYMENT_INITIATE,
        self::PAYMENT_CONFIRM_CASH,
        self::PAYMENT_OVERRIDE,
        self::PAYMENT_REFUND,
        self::INVENTORY_VIEW,
        self::INVENTORY_UPDATE,
        self::LOCATION_VIEW,
        self::LOCATION_MANAGE,
        self::DISCOUNT_MANAGE,
        self::REPORT_VIEW,
        self::REPORT_EXPORT,
        self::USER_MANAGE,
        self::SETTING_MANAGE,
        self::AUDIT_VIEW,
    ];

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_CASHIER = 'cashier';

    public const ROLE_KITCHEN = 'kitchen';

    /**
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        return [
            self::ROLE_ADMIN => self::ALL,
            self::ROLE_MANAGER => [
                self::MENU_VIEW,
                self::CATALOG_VIEW,
                self::CATALOG_MANAGE,
                self::ORDER_VIEW,
                self::ORDER_ACCEPT,
                self::ORDER_CANCEL,
                self::ORDER_UPDATE_STATUS,
                self::KITCHEN_VIEW,
                self::PAYMENT_INITIATE,
                self::PAYMENT_CONFIRM_CASH,
                self::PAYMENT_REFUND,
                self::INVENTORY_VIEW,
                self::INVENTORY_UPDATE,
                self::LOCATION_VIEW,
                self::LOCATION_MANAGE,
                self::DISCOUNT_MANAGE,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
                self::AUDIT_VIEW,
                self::SETTING_MANAGE,
            ],
            self::ROLE_CASHIER => [
                self::MENU_VIEW,
                self::CATALOG_VIEW,
                self::ORDER_VIEW,
                self::ORDER_ACCEPT,
                self::ORDER_CANCEL,
                self::ORDER_UPDATE_STATUS,
                self::PAYMENT_INITIATE,
                self::PAYMENT_CONFIRM_CASH,
                self::INVENTORY_VIEW,
                self::LOCATION_VIEW,
                self::REPORT_VIEW,
            ],
            self::ROLE_KITCHEN => [
                self::MENU_VIEW,
                self::CATALOG_VIEW,
                self::ORDER_VIEW,
                self::ORDER_UPDATE_STATUS,
                self::KITCHEN_VIEW,
                self::KITCHEN_UPDATE_STATUS,
                self::INVENTORY_VIEW,
            ],
        ];
    }
}
