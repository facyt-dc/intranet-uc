import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function EditCategory({ auth, category }) {
    const { data, setData, put, processing, errors } = useForm({
        name: category.name || '',
        description: category.description || ''
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('admin.item-category.update', category.id));
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={`Editar Categoría: ${category.name}`} />
            <div>
                <h1 className="text-2xl font-bold mb-4">Editar Categoría</h1>
                <form onSubmit={submit} className="space-y-4 max-w-2xl">
                    <div>
                        <label htmlFor="name" className="block font-medium text-sm text-gray-700">Nombre</label>
                        <input
                            id="name"
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        />
                        {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                    </div>
                    <div>
                        <label htmlFor="description" className="block font-medium text-sm text-gray-700">Descripción</label>
                        <textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        ></textarea>
                        {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                    </div>
                    <div className="flex gap-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            Actualizar
                        </button>
                        <Link href={route('admin.item-category.index')} className="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </AdminLayout>
        
    );
}
