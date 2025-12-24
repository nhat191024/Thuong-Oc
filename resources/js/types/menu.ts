export interface Menu {
    id: number;
    name: string;
    foods: Food[];
}

export interface Food {
    id: number;
    name: string;
    is_favorite: boolean;
    is_discounted: boolean;
    price: number;
    discount_price: number;
    note: string | null;
    image: string | null;
    sold_count: number;
    dishes: Dish[];
}

export interface Dish {
    id: number;
    name: string;
    additional_price: number;
    note: string | null;
    cooking_method_id: number;
}

