import React from 'react';
import { useForm, Link, Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Create({ auth, categories, statuses, locations }) {
    // 1. CORREGIR los nombres de los campos en el estado inicial de useForm
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        serial_number: '',
        description: '',
        category_id: '',        
        current_status_id: '',  
        current_location_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.item.store'));
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Crear Ítem" />
            <div>
                <h1 className="text-2xl font-bold mb-4">Crear Nuevo Ítem</h1>
                <form onSubmit={submit} className="space-y-4 max-w-2xl">
                    <div>
                        <label className="block font-medium">Nombre</label>
                        <input
                            id="name"
                            type="text"
                            className="w-full border rounded px-3 py-2"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
                    </div>
                    
                    {/* ... otros campos como serial_number, description ... */}

                    <div>
                        <label className="block font-medium">Descripción</label>
                        <textarea
                            id="description"
                            className="w-full border rounded px-3 py-2"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
                    </div>

                    {/* Select para Categoría */}
                    <div>
                        <label className="block font-medium">Categoría</label>
                        <select
                            className="w-full border rounded px-3 py-2"
                            value={data.category_id} // 2. CORREGIR el 'value'
                            onChange={(e) => setData('category_id', e.target.value)} // 3. CORREGIR el 'setData'
                        >
                            <option value="">Seleccione una categoría...</option>
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.name}
                                </option>
                            ))}
                        </select>
                        
                        {errors.category_id && <p className="text-red-500 text-sm mt-1">{errors.category_id}</p>}
                    </div>

                    {/* Select para Estado */}
                    <div>
                        <label className="block font-medium">Estado</label>
                        <select
                            className="w-full border rounded px-3 py-2"
                            value={data.current_status_id}
                            onChange={(e) => setData('current_status_id', e.target.value)}
                        >
                            <option value="">Seleccione un estado...</option>
                            {statuses.map((status) => (
                                <option key={status.id} value={status.id}>
                                    {status.name}
                                </option>
                            ))}
                        </select>
                        {errors.current_status_id && <p className="text-red-500 text-sm mt-1">{errors.current_status_id}</p>}
                    </div>

                    {/* Select para Ubicación */}
                    <div>
                        <label className="block font-medium">Ubicación</label>
                        <select
                            className="w-full border rounded px-3 py-2"
                            value={data.current_location_id}
                            onChange={(e) => setData('current_location_id', e.target.value)}
                        >
                            <option value="">Seleccione una ubicación...</option>
                            {locations.map((loc) => (
                                <option key={loc.id} value={loc.id}>
                                    {loc.name}
                                </option>
                            ))}
                        </select>
                        {errors.current_location_id && <p className="text-red-500 text-sm mt-1">{errors.current_location_id}</p>}
                    </div>

                    <div className="flex gap-4 mt-6">
                        <button type="submit" disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Crear
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