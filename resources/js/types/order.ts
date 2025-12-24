export interface orderDish {
    table: number;
    foodId: number;
    dishId: number | null;
    name: string;
    quantity: number;
    price: number;
    cookingMethod: string | null;
    cookingMethodId: number | null;
    note: string | null;
}
