import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router, useForm } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import React, { useState, useEffect, Fragment } from 'react';
import { Popover, Transition } from '@headlessui/react';
import FilterDisclosure from '@/Components/FilterDisclosure';
import { useTranslation, Trans } from 'react-i18next';

const formatDate = (dateString) => {
    if (!dateString) {
        return <span className="text-gray-400">
                    <Trans i18nKey="NotDefined">
                        Not defined
                    </Trans>
                </span>;
    }
    try {
        const date = parseISO(dateString);
        return format(date, 'dd/MM/yyyy');
    } catch (error) {
        return dateString;
    }
};
const FilterIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5"><path fillRule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clipRule="evenodd" /></svg>
);

const ConfirmDeleteModal = ({ isOpen, onClose, onConfirm, itemName, processing, errorMessage }) => {
    const { t } = useTranslation();
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                {errorMessage ? (
                    <>
                        <h3 className="text-lg font-bold text-red-700">{t('Error Deleting')}</h3>
                        <div className="mt-2"><p className="text-sm text-gray-800">{errorMessage}</p></div>
                        <div className="mt-6 flex justify-end">
                            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700">{t('Continue')}</button>
                        </div>
                    </>
                ) : (
                    <>
                        <h3 className="text-lg font-bold text-gray-900">{t('Confirm Deletion')}</h3>
                        <div className="mt-2">
                            <p className="text-sm text-gray-600">
                                <Trans i18nKey="confirmDeleteEquipmentText" values={{ itemName }}>
                                    Are you sure you want to delete the equipment <strong>"{itemName}"</strong>? This action cannot be undone.
                                </Trans>
                            </p>
                        </div>
                        <div className="mt-6 flex justify-end gap-4">
                            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{t('Cancel')}</button>
                            <button type="button" onClick={onConfirm} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:bg-red-300">
                                {processing ? t('Deleting...') : t('Confirm Deletion')}
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

const EquipmentRow = ({ equipment }) => {
    const { t } = useTranslation();
    const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
    const [modalErrorMessage, setModalErrorMessage] = useState(null);
    const { delete: destroy, processing: isDeleting } = useForm();

    const handleDeleteConfirm = () => {
        destroy(route('mantenimiento.equipment.destroy', equipment.id), {
            onSuccess: () => {
                setDeleteModalOpen(false);
                setModalErrorMessage(null);
            },
            onError: (errors) => {
                setModalErrorMessage(errors.error || t('An unknown error occurred.'));
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
    const handleRowClick = (equipmentId) => {
        router.visit(route('mantenimiento.equipment.show', equipmentId));
    };
    return (
        <>
            <tr onClick={() => handleRowClick(equipment.id)} className="cursor-pointer hover:bg-gray-50 transition-colors duration-150">
                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{equipment.name}</td>
                <td className="px-6 py-4 whitespace-nowrap text-gray-600">{equipment.brand || 'N/A'} / {equipment.model || 'N/A'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-gray-600">{equipment.category?.name || 'N/A'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-gray-600">{formatDate(equipment.next_maintenance_at)}</td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                    <Link href={route('mantenimiento.equipment.edit', equipment.id)} className="text-indigo-600 hover:text-indigo-900" onClick={(e) => e.stopPropagation()}>
                        {t('Edit')}
                    </Link>
                    <button onClick={(e) => { e.stopPropagation(); openDeleteModal(); }} className="text-red-600 hover:text-red-900">
                        {t('Delete')}
                    </button>
                </td>
            </tr>
            <ConfirmDeleteModal
                isOpen={isDeleteModalOpen}
                onClose={closeDeleteModal}
                onConfirm={handleDeleteConfirm}
                itemName={equipment.name}
                processing={isDeleting}
                errorMessage={modalErrorMessage}
            />
        </>
    );
};

export default function Index({ auth, equipments, categories, filters }) {

    const [filterValues, setFilterValues] = useState({
        search: filters.search || '',
        category: filters.category || '',
    });
    const { t } = useTranslation();
    useEffect(() => {
        const handler = setTimeout(() => {
            router.get(route('mantenimiento.equipment.index'), filterValues, {
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(handler);
    }, [filterValues]);


    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilterValues(prev => ({ ...prev, [name]: value }));
    };

    const resetFilters = () => {
        setFilterValues({ search: '', category: '' });
    };

    const { search, ...dropdownFilters } = filterValues;
    const activeFilterCount = Object.values(dropdownFilters).filter(Boolean).length;

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">{t('Maintenance Equipment')}</h2>
                </div>
            }
        >
            <Head title={t('Equipment')} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                    <div className="flex items-center gap-4">
                        <Link href={route('mantenimiento.equipment.create')} className="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700">
                            {t('Create Equipment')}
                        </Link>
                        <input
                            type="text"
                            name="search"
                            value={filterValues.search}
                            onChange={handleFilterChange}
                            placeholder={t('Search equipment...')}
                            className="form-input rounded-md shadow-sm text-sm"
                        />
                        <div className="ml-auto">
                            <Popover className="relative">
                                <Popover.Button className="inline-flex items-center gap-x-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none">
                                    <FilterIcon />
                                    {t('Filters')}
                                    {activeFilterCount > 0 && <span className="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-500 rounded-full">{activeFilterCount}</span>}
                                </Popover.Button>
                                <Transition as={Fragment} /*...*/ >
                                    <Popover.Panel className="absolute right-0 z-10 mt-2 w-72 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                        <div className="p-4 text-lg font-semibold border-b">{t('Filter Equipment')}</div>
                                        <FilterDisclosure title={t('Category')} options={categories} selectedValue={filterValues.category} onSelect={(value) => handleFilterChange('category', value)} />
                                        <div className="p-2 bg-gray-50 border-t">
                                            <button onClick={resetFilters} className="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">{t('Clear Filters')}</button>
                                        </div>
                                    </Popover.Panel>
                                </Transition>
                            </Popover>
                        </div>
                    </div>
                </div>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{t('Name')}</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{t('Brand / Model')}</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{t('Category')}</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{t('Next Maintenance')}</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{t('Actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {equipments.map((item) => (
                                    <EquipmentRow key={item.id} equipment={item} />
                                ))}
                                {equipments.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-4 text-center text-gray-500">{t('No equipment found.')}</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}