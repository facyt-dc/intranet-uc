import React from 'react';
import { useForm, Link, Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout'; // Importa el Layout

export default function Edit({ auth, item, categories, statuses, locations }) {
    const { data, setData, put, processing, errors } = useForm({
        name: item.name,
        description: item.description,
        category_id: item.category_id,
        current_status_id: item.current_status_id,
        current_location_id: item.current_location_id,

    });

    const submit = (e) => {
        e.preventDefault();
        put(route('admin.item.update', item.id));
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Editar Ítem" />
            <div>
                <h1 className="text-2xl font-semibold mb-4">Editar Ítem</h1>
                <form onSubmit={submit} className="space-y-4 max-w-2xl">
                    <div>
                        <label className="block font-medium text-sm text-gray-700">Nombre</label>
                        <input
                            id="name"
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        />
                        {errors.name && <p className="text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="block font-medium text-sm text-gray-700">Descripción</label>
                        <textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label className="block font-medium text-sm text-gray-700">Categoría</label>
                        <select
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            value={data.category_id}
                            onChange={(e) => setData('category_id', e.target.value)}
                        >
                            <option value="">Seleccione...</option>
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.name}
                                </option>
                            ))}
                        </select>
                        {errors.category_id && <p className="text-red-500">{errors.category_id}</p>}
                    </div>
                    <div>
                        <label className="block font-medium text-sm text-gray-700">Estado</label>
                        <select
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            value={data.current_status_id}
                            onChange={(e) => setData('current_status_id', e.target.value)}
                        >
                            <option value="">Seleccione...</option>
                            {statuses.map((status) => (
                                <option key={status.id} value={status.id}>
                                    {status.name}
                                </option>
                            ))}
                        </select>
                        {errors.current_status_id && <p className="text-red-500">{errors.current_status_id}</p>}
                    </div>
                    <div>
                        <label className="block font-medium text-sm text-gray-700">Ubicación</label>
                        <select
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            value={data.current_location_id}
                            onChange={(e) => setData('current_location_id', e.target.value)}
                        >
                            <option value="">Seleccione...</option>
                            {locations.map((loc) => (
                                <option key={loc.id} value={loc.id}>
                                    {loc.name}
                                </option>
                            ))}
                        </select>
                        {errors.current_location_id && <p className="text-red-500">{errors.current_location_id}</p>}
                    </div>
                    <div className="flex gap-4">
                        <button type="submit" disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Actualizar
                        </button>
                        <Link href={route('admin.item.index')} className="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}