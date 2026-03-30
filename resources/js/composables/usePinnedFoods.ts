import { computed, ref } from 'vue';
import { Food, Menu } from '@/types/menu';

const STORAGE_KEY = 'staff:pinned_food_ids';

function loadPinnedIds(): number[] {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? (JSON.parse(stored) as number[]) : [];
    } catch {
        return [];
    }
}

// Shared reactive state across all component instances
const pinnedIds = ref<number[]>(loadPinnedIds());

function savePinnedIds(): void {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(pinnedIds.value));
}

export function usePinnedFoods() {
    const pinnedFoodsFromMenus = (menus: Menu[]): Food[] => {
        const allFoods = menus.flatMap((m) => m.foods);
        return pinnedIds.value.map((id) => allFoods.find((f) => f.id === id)).filter((f): f is Food => f !== undefined);
    };

    function isPinned(foodId: number): boolean {
        return pinnedIds.value.includes(foodId);
    }

    function togglePin(food: Food): void {
        if (isPinned(food.id)) {
            pinnedIds.value = pinnedIds.value.filter((id) => id !== food.id);
        } else {
            pinnedIds.value = [...pinnedIds.value, food.id];
        }
        savePinnedIds();
    }

    function unpin(foodId: number): void {
        pinnedIds.value = pinnedIds.value.filter((id) => id !== foodId);
        savePinnedIds();
    }

    return {
        pinnedIds: computed(() => pinnedIds.value),
        pinnedFoodsFromMenus,
        isPinned,
        togglePin,
        unpin,
    };
}
