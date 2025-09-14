import React, { useState, useEffect } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router, useForm } from '@inertiajs/react';
import AdvancedFilterMenu from "@/Components/AdvancedFilterMenu"; // Reutilizamos el menú!
import { useTranslation, Trans } from 'react-i18next';

const ConfirmDeleteModal = ({ isOpen, onClose, onConfirm, itemName, processing }) => {
    const { t } = useTranslation();
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 className="text-lg font-bold text-gray-900">{t('Confirm Deletion')}</h3>
                <div className="mt-2">
                    <p className="text-sm text-gray-600">
                        <Trans i18nKey="confirmDeleteArchivedText" values={{ itemName }}>
                            Are you sure you want to permanently delete the request <strong>"{itemName}"</strong>? This action cannot be undone.
                        </Trans>
                    </p>
                </div>
                <div className="mt-6 flex justify-end gap-4">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{t('Cancel')}</button>
                    <button type="button" onClick={onConfirm} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:bg-red-300">
                        {processing ? t('Deleting...') : t('Confirm Deletion')}
                    </button>
                </div>
            </div>
        </div>
    );
};

const ArchivedRequestRow = ({ request }) => {
    const { t } = useTranslation();
    const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
    const { delete: destroy, processing: isDeleting } = useForm();

    const handleDeleteConfirm = () => {
        destroy(route('mantenimiento.destroy', request.id), {
            onSuccess: () => setDeleteModalOpen(false),
            preserveScroll: true,
        });
    };

    return (
        <>
            <tr>
                <td className="px-6 py-4 font-medium text-gray-900">{request.title}</td>
                <td className="px-6 py-4 text-gray-600">{request.equipment?.name || 'N/A'}</td>
                <td className="px-6 py-4 text-gray-600">{request.stage?.name || 'N/A'}</td>
                <td className="px-6 py-4 text-right text-sm font-medium space-x-4">
                    <Link href={route('mantenimiento.show', { maintenanceRequest: request.id, from: 'archived' })} className="text-indigo-600 hover:text-indigo-900">
                        {t('View Details')}
                    </Link>
                    <button onClick={() => setDeleteModalOpen(true)} className="text-red-600 hover:text-red-900">
                        {t('Delete')}
                    </button>
                </td>
            </tr>
            <ConfirmDeleteModal
                isOpen={isDeleteModalOpen}
                onClose={() => setDeleteModalOpen(false)}
                onConfirm={handleDeleteConfirm}
                itemName={request.title}
                processing={isDeleting}
            />
        </>
    );
};

export default function ArchivedIndex({ auth, requests, technicians, equipments, equipmentCategories, filters }) {
    const { t } = useTranslation();
    const [filterValues, setFilterValues] = useState({
        search: filters.search || '',
        technician: filters.technician || '',
        equipment: filters.equipment || '',
        category: filters.category || '',
    });

    useEffect(() => {
        const handler = setTimeout(() => {
            // Apunta a la nueva ruta de archivados
            router.get(route('mantenimiento.archived.index'), filterValues, {
                preserveState: true,
                replace: true,
            });
        }, 300);
        return () => clearTimeout(handler);
    }, [filterValues]);

    const handleFilterChange = (name, value) => {
        setFilterValues(prev => ({ ...prev, [name]: value }));
    };
    
    const resetFilters = () => {
        setFilterValues({ search: '', technician: '', equipment: '', category: '' });
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">{t('Archived Requests')}</h2>
                    <Link href={route('mantenimiento.index')} className="text-sm font-semibold">&larr; {t('Back to Kanban')}</Link>
                </div>
            }
        >
            <Head title={t('Archived Requests')} />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                    <div className="flex items-center gap-4">
                        <input type="text" value={filterValues.search} onChange={(e) => handleFilterChange('search', e.target.value)} placeholder={t('Search in archived...')} className="form-input rounded-md shadow-sm text-sm w-full md:w-80" />
                        <div className="ml-auto">
                            <AdvancedFilterMenu
                                filterValues={filterValues}
                                onFilterChange={handleFilterChange}
                                onResetFilters={resetFilters}
                                technicians={technicians}
                                equipments={equipments}
                                equipmentCategories={equipmentCategories}
                            />
                        </div>
                    </div>
                </div>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{t('Title')}</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{t('Equipment')}</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{t('Stage')}</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{t('Actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {requests.map((req) => (
                                    <ArchivedRequestRow key={req.id} request={req} />
                                ))}
                                {requests.length === 0 && (
                                    <tr><td colSpan="4" className="px-6 py-4 text-center text-gray-500">{t('No archived requests found.')}</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}