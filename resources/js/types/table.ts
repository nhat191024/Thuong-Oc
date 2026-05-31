export interface Table {
    id: string;
    name: string;
    table_number: number;
    branch_id: number;
    is_active: string | number; // TableActiveStatus
}
