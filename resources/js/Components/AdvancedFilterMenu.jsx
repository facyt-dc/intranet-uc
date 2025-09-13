import React, { Fragment } from 'react';
import { Popover, Transition } from '@headlessui/react';
import FilterDisclosure from './FilterDisclosure';

const FilterIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5">
      <path fillRule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clipRule="evenodd" />
    </svg>
);


export default function AdvancedFilterMenu({ filterValues, onFilterChange, onResetFilters, technicians, equipments, equipmentCategories }) {
    
    // Contar filtros activos para la notificación
    const { search, ...dropdownFilters } = filterValues;
    const activeFilterCount = Object.values(dropdownFilters).filter(Boolean).length;

    return (
        <Popover className="relative">
            <Popover.Button className="inline-flex items-center gap-x-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none">
                <FilterIcon />
                Filtros
                {activeFilterCount > 0 && (
                    <span className="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-500 rounded-full">
                        {activeFilterCount}
                    </span>
                )}
            </Popover.Button>
            <Transition
                as={Fragment}
                enter="transition ease-out duration-200"
                enterFrom="opacity-0 translate-y-1"
                enterTo="opacity-100 translate-y-0"
                leave="transition ease-in duration-150"
                leaveFrom="opacity-100 translate-y-0"
                leaveTo="opacity-0 translate-y-1"
            >
                <Popover.Panel className="absolute right-0 z-10 mt-2 w-72 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div className="p-4 text-lg font-semibold border-b">
                        Filtrar Solicitudes
                    </div>
                    
                    <FilterDisclosure
                        title="Técnico"
                        options={technicians}
                        selectedValue={filterValues.technician}
                        onSelect={(value) => onFilterChange('technician', value)}
                    />
                    <FilterDisclosure
                        title="Equipo"
                        options={equipments}
                        selectedValue={filterValues.equipment}
                        onSelect={(value) => onFilterChange('equipment', value)}
                    />
                    <FilterDisclosure
                        title="Categoría"
                        options={equipmentCategories}
                        selectedValue={filterValues.category}
                        onSelect={(value) => onFilterChange('category', value)}
                    />

                    <div className="p-2 bg-gray-50 border-t">
                        <button
                            onClick={onResetFilters}
                            className="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                        >
                            Limpiar todos los filtros
                        </button>
                    </div>
                </Popover.Panel>
            </Transition>
        </Popover>
    );
}