import React from 'react';
import { useForm } from '@inertiajs/react';

export default function EditInventoryMovement({ movement, items, users, movementTypes }) {
    const { data, setData, put, processing, errors } = useForm({
        item_id: movement.item_id,
        user_id: movement.user_id,
        movement_type_id: movement.movement_type_id,
        description: movement.description,
        movement_date: movement.movement_date,
        details: movement.details,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('admin.item-inventory-movement.update', movement.id));
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Editar Movimiento de Inventario" />
            <div className="max-w-3xl mx-auto mt-10">
                <h1 className="text-2xl font-semibold mb-6">Editar Movimiento</h1>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block">Ítem</label>
                        <select className="w-full" value={data.item_id} onChange={(e) => setData('item_id', e.target.value)}>
                            <option value="">Seleccione...</option>
                            {items.map(item => (
                                <option key={item.id} value={item.id}>{item.name}</option>
                            ))}
                        </select>
                        {errors.item_id && <div className="text-red-500 text-sm">{errors.item_id}</div>}
                    </div>
                    <div>
                        <label className="block">Usuario</label>
                        <select className="w-full" value={data.user_id} onChange={(e) => setData('user_id', e.target.value)}>
                            <option value="">Seleccione...</option>
                            {users.map(user => (
                                <option key={user.id} value={user.id}>{user.name}</option>
                            ))}
                        </select>
                        {errors.user_id && <div className="text-red-500 text-sm">{errors.user_id}</div>}
                    </div>
                    <div>
                        <label className="block">Tipo de Movimiento</label>
                        <select className="w-full" value={data.movement_type_id} onChange={(e) => setData('movement_type_id', e.target.value)}>
                            <option value="">Seleccione...</option>
                            {movementTypes.map(type => (
                                <option key={type.id} value={type.id}>{type.name}</option>
                            ))}
                        </select>
                        {errors.movement_type_id && <div className="text-red-500 text-sm">{errors.movement_type_id}</div>}
                    </div>
                    <div>
                        <label className="block">Fecha del Movimiento</label>
                        <input type="datetime-local" className="w-full" value={data.movement_date} onChange={(e) => setData('movement_date', e.target.value)} />
                        {errors.movement_date && <div className="text-red-500 text-sm">{errors.movement_date}</div>}
                    </div>
                    <div>
                        <label className="block">Descripción</label>
                        <textarea className="w-full" value={data.description} onChange={(e) => setData('description', e.target.value)} />
                        {errors.description && <div className="text-red-500 text-sm">{errors.description}</div>}
                    </div>
                    <div>
                        <label className="block">Detalles (JSON)</label>
                        <textarea className="w-full" value={data.details} onChange={(e) => setData('details', e.target.value)} />
                        {errors.details && <div className="text-red-500 text-sm">{errors.details}</div>}
                    </div>
                    <button type="submit" disabled={processing} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </form>
            </div>
        </AdminLayout>
    );
}
