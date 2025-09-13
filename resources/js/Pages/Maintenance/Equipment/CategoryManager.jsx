import React, { useState } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm } from '@inertiajs/react';

const ConfirmDeleteModal = ({ isOpen, onClose, onConfirm, itemName, processing }) => {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 className="text-lg font-bold text-gray-900">Confirmar Eliminación</h3>
                <div className="mt-2">
                    <p className="text-sm text-gray-600">
                        ¿Estás seguro de que quieres eliminar la categoría <strong>"{itemName}"</strong>? Esta acción no se puede deshacer.
                    </p>
                </div>
                <div className="mt-6 flex justify-end gap-4">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" onClick={onConfirm} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:bg-red-300">
                        {processing ? 'Eliminando...' : 'Confirmar'}
                    </button>
                </div>
            </div>
        </div>
    );
};

const CategoryRow = ({ category }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
    const { data, setData, put, processing } = useForm({ name: category.name });
    const { delete: destroy, processing: isDeleting } = useForm();

    const handleUpdate = (e) => {
        e.preventDefault();
        put(route('mantenimiento.equipment.categories.update', category.id), {
            onSuccess: () => setIsEditing(false),
            preserveScroll: true,
        });
    };

    const handleDeleteConfirm = () => {
        destroy(route('mantenimiento.equipment.categories.destroy', category.id), {
            onSuccess: () => setDeleteModalOpen(false),
            preserveScroll: true,
        });
    };

    return (
        <div className="flex items-center justify-between p-4 border-b">
            {isEditing ? (
                <form onSubmit={handleUpdate} className="flex-grow flex items-center gap-4">
                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="form-input rounded-md shadow-sm flex-grow" autoFocus />
                    <button type="submit" disabled={processing} className="px-3 py-1 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600">Guardar</button>
                    <button type="button" onClick={() => setIsEditing(false)} className="text-sm text-gray-600 hover:underline">Cancelar</button>
                </form>
            ) : (
                <>
                    <span className="font-bold">{category.name}</span>
                    <div className="flex items-center gap-4">
                        <button onClick={() => setIsEditing(true)} className="text-sm font-medium text-indigo-600 hover:text-indigo-900">Editar</button>
                        <button onClick={() => setDeleteModalOpen(true)} className="text-sm font-medium text-red-600 hover:text-red-900">Eliminar</button>
                    </div>
                </>
            )}
            <ConfirmDeleteModal
                isOpen={isDeleteModalOpen}
                onClose={() => setDeleteModalOpen(false)}
                onConfirm={handleDeleteConfirm}
                itemName={category.name}
                processing={isDeleting}
            />
        </div>
    );
};

export default function CategoryManager({ auth, categories }) {
    const { data: newData, setData: setNewData, post, processing: newProcessing, errors: newErrors, reset: resetNew } = useForm({
        name: '',
    });

    const handleCreate = (e) => {
        e.preventDefault();
        post(route('mantenimiento.equipment.categories.store'), {
            onSuccess: () => resetNew('name'),
        });
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Gestionar Categorías de Equipos</h2>
                    <Link href={route('mantenimiento.equipment.index')} className="text-sm font-semibold">&larr; Volver a Equipos</Link>
                </div>
            }
        >
            <Head title="Gestionar Categorías" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white p-6 rounded-lg shadow">
                        <h3 className="text-lg font-bold mb-4">Crear Nueva Categoría</h3>
                        <form onSubmit={handleCreate} className="flex items-start gap-4">
                            <div className="flex-grow">
                                <label htmlFor="newName" className="sr-only">Nombre</label>
                                <input id="newName" type="text" placeholder="Nombre de la nueva categoría" value={newData.name} onChange={e => setNewData('name', e.target.value)} className="form-input w-full rounded-md shadow-sm" />
                                {newErrors.name && <p className="text-sm text-red-600 mt-1">{newErrors.name}</p>}
                            </div>
                            <button type="submit" disabled={newProcessing} className="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-sm hover:bg-gray-700 disabled:bg-gray-400">
                                {newProcessing ? 'Creando...' : 'Crear'}
                            </button>
                        </form>
                    </div>
                    <div className="bg-white rounded-lg shadow">
                        {categories.map(cat => (
                            <CategoryRow key={cat.id} category={cat} />
                        ))}
                        {categories.length === 0 && <p className="p-4 text-gray-500">No hay categorías definidas.</p>}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}