import React, { useState } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { useTranslation, Trans } from 'react-i18next';

const ConfirmDeleteModal = ({ isOpen, onClose, onConfirm, itemName, processing, errorMessage }) => {
    const { t } = useTranslation();

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                {/* --- RENDERIZADO CONDICIONAL --- */}
                {errorMessage ? (
                    <>
                        <h3 className="text-lg font-bold text-red-700">{t('Error Deleting')}</h3>
                        <div className="mt-2">
                            <p className="text-sm text-gray-800">{errorMessage}</p>
                        </div>
                        <div className="mt-6 flex justify-end gap-4">
                            <button
                                type="button"
                                onClick={onClose}
                                className="px-4 py-2 text-sm font-medium text-white bg-gray-600 border border-transparent rounded-md hover:bg-gray-700"
                            >
                                {t('Continue')}
                            </button>
                        </div>
                    </>
                ) : (
                    <>
                        <h3 className="text-lg font-bold text-gray-900">{t('Confirm Deletion')}</h3>
                        <div className="mt-2">
                            <p className="text-sm text-gray-600">
                                <Trans i18nKey="confirmDeleteCategoryText" values={{ itemName }}>
                                    Are you sure you want to delete the category <strong>"{itemName}"</strong>? This action cannot be undone.
                                </Trans>
                            </p>
                        </div>
                        <div className="mt-6 flex justify-end gap-4">
                            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                {t('Cancel')}
                            </button>
                            <button type="button" onClick={onConfirm} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:bg-red-300">
                                {processing ? t('Deleting...') : t('Confirm')}
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

const CategoryRow = ({ category }) => {
    const { t } = useTranslation(); // Hook de traducción
    const [isEditing, setIsEditing] = useState(false);
    const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
    const [modalErrorMessage, setModalErrorMessage] = useState(null);
    const { data, setData, put, processing } = useForm({ name: category.name });
    const { delete: destroy, processing: isDeleting } = useForm();
    const { props } = usePage();
    const handleUpdate = (e) => {
        e.preventDefault();
        put(route('mantenimiento.equipment.categories.update', category.id), {
            onSuccess: () => setIsEditing(false),
            preserveScroll: true,
        });
    };

    const handleDeleteConfirm = () => {
        destroy(route('mantenimiento.equipment.categories.destroy', category.id), {
            onSuccess: () => {
                setDeleteModalOpen(false);
                setModalErrorMessage(null);
            },
            onError: (errors) => {
                if (errors && errors.error) {
                    setModalErrorMessage(errors.error);
                } else {
                    setModalErrorMessage(t('An unknown error occurred.'));
                }
            },
            preserveScroll: true,
        });
    };
    const openDeleteModal = () => {
        setModalErrorMessage(null);
        setDeleteModalOpen(true);
    };

    const closeDeleteModal = () => {
        setDeleteModalOpen(false);
        setModalErrorMessage(null);
    };

    return (
        <div className="flex items-center justify-between p-4 border-b">
            {isEditing ? (
                <form onSubmit={handleUpdate} className="flex-grow flex items-center gap-4">
                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="form-input rounded-md shadow-sm flex-grow" autoFocus />
                    <button type="submit" disabled={processing} className="px-3 py-1 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600">{t('Save')}</button>
                    <button type="button" onClick={() => setIsEditing(false)} className="text-sm text-gray-600 hover:underline">{t('Cancel')}</button>
                </form>
            ) : (
                <>
                    <span className="font-bold">{category.name}</span>
                    <div className="flex items-center gap-4">
                        <button onClick={() => setIsEditing(true)} className="text-sm font-medium text-indigo-600 hover:text-indigo-900">{t('Edit')}</button>
                        <button onClick={openDeleteModal} className="text-sm font-medium text-red-600 hover:text-red-900">{t('Delete')}</button>
                    </div>
                </>
            )}
            <ConfirmDeleteModal
                isOpen={isDeleteModalOpen}
                onClose={closeDeleteModal}
                onConfirm={handleDeleteConfirm}
                itemName={category.name}
                processing={isDeleting}
                errorMessage={modalErrorMessage}
            />
        </div>
    );
};

export default function CategoryManager({ auth, categories }) {
    const { t } = useTranslation(); // Hook de traducción
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
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">{t('Manage Equipment Categories')}</h2>
                    <Link href={route('mantenimiento.equipment.index')} className="text-sm font-semibold">&larr; {t('Back to Equipment')}</Link>
                </div>
            }
        >
            <Head title={t('Manage Categories')} />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white p-6 rounded-lg shadow">
                        <h3 className="text-lg font-bold mb-4">{t('Create New Category')}</h3>
                        <form onSubmit={handleCreate} className="flex items-start gap-4">
                            <div className="flex-grow">
                                <label htmlFor="newName" className="sr-only">{t('Name')}</label>
                                <input id="newName" type="text" placeholder={t('New category name')} value={newData.name} onChange={e => setNewData('name', e.target.value)} className="form-input w-full rounded-md shadow-sm" />
                                {newErrors.name && <p className="text-sm text-red-600 mt-1">{newErrors.name}</p>}
                            </div>
                            <button type="submit" disabled={newProcessing} className="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-sm hover:bg-gray-700 disabled:bg-gray-400">
                                {newProcessing ? t('Creating...') : t('Create')}
                            </button>
                        </form>
                    </div>
                    <div className="bg-white rounded-lg shadow">
                        {categories.map(cat => (
                            <CategoryRow key={cat.id} category={cat} />
                        ))}
                        {categories.length === 0 && <p className="p-4 text-gray-500">{t('No categories defined.')}</p>}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}