export interface orderDish {
    table: number;
    foodId: number | null;
    dishId: number | null;
    custom_dish_name?: string | null;
    custom_kitchen_id?: number | null;
    name: string;
    quantity: number;
    price: number;
    cookingMethod: string | null;
    cookingMethodId: number | null;
    note: string | null;
}
