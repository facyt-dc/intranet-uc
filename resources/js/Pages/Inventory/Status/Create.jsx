import React from 'react';
import { Link, useForm, Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout'; // Importa el Layout


export default function Create({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
        is_operational: true,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.item-status.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Crear Estado" />
            <div>
                <h1 className="text-2xl font-bold mb-4">Crear Estado</h1>
                <form onSubmit={submit} className="space-y-4 max-w-2xl">
                    <div>
                        <label htmlFor="name" className="block font-medium">Nombre</label>
                        <input
                            id="name"
                            type="text"
                            className="w-full border rounded px-3 py-2"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label htmlFor="description" className="block font-medium">Descripción</label>
                        <textarea
                            id="description"
                            className="w-full border rounded px-3 py-2"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
                    </div>
                    <div className="flex items-center space-x-2">
                        <input
                            id="is_operational"
                            type="checkbox"
                            checked={data.is_operational}
                            onChange={(e) => setData('is_operational', e.target.checked)}
                        />
                        <label htmlFor="is_operational" className="font-semibold">¿Es operativo?</label>
                    </div>
                    <div className="flex gap-4 mt-6">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            Crear
                        </button>

                        <Link
                            href={route('admin.item-status.index')}
                            className="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
                        >
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
