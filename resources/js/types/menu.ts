export interface Menu {
    id: number;
    name: string;
    foods: Food[];
}

export interface Food {
    id: number;
    name: string;
    price: number;
    note: string | null;
    image: string | null;
    dishes: Dish[];
}

export interface Dish {
    id: number;
    name: string;
    additional_price: number;
    note: string | null;
    cooking_method_id: number;
}

