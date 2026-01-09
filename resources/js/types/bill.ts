import { Table } from './table';
import { Dish, Food } from './menu';

export interface Branch {
    id: number;
    name: string;
}

export interface BillDetail {
    id: number;
    bill_id: number;
    dish_id: number | null;
    custom_dish_name: string | null;
    quantity: number;
    price: number;
    note: string | null;
    created_at: string;
    updated_at: string;
    dish?: Dish;
    // When eager loaded as 'billDetails.dish.food'
    // Dish might be merged or contain Food.
    // In menu.ts, Dish doesn't have food property.
    // But backend response will have it.
    // We should extend Dish or define a specific type for this usage.
}

// Extending Dish for the response structure
export interface BillDetailDish extends Dish {
    food?: Food;
}

export interface BillDetailWithRelations extends BillDetail {
    dish?: BillDetailDish;
}

export interface Bill {
    id: number;
    table_id: string;
    branch_id: number;
    user_id: number | null;
    customer_id: number | null;
    time_in: string;
    time_out: string | null;
    total: number;
    discount: number | null;
    final_total: number;
    payment_method: string | null; // PaymentMethods enum value
    pay_status: string; // PayStatus enum value
    voucher_id: number | null;
    created_at: string;
    updated_at: string;
    branch?: Branch;
    table?: Table;
    bill_details?: BillDetailWithRelations[];
}
