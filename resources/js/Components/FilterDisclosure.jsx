import React, { useState, Fragment } from 'react';
import { Disclosure, Transition } from '@headlessui/react';

const ChevronUpIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5">
        <path fillRule="evenodd" d="M14.77 12.78a.75.75 0 0 1-1.06 0L10 9.06l-3.72 3.72a.75.75 0 1 1-1.06-1.06l4.25-4.25a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06Z" clipRule="evenodd" />
    </svg>
);


export default function FilterDisclosure({ title, options, selectedValue, onSelect }) {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredOptions = options.filter(option =>
        option.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    const selectedOptionName = options.find(opt => opt.id == selectedValue)?.name || 'Todos';

    return (
        <Disclosure as="div" className="border-t border-gray-200">
            {({ open }) => (
                <>
                    <Disclosure.Button className="flex w-full items-center justify-between bg-gray-50 px-4 py-3 text-left text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus-visible:ring focus-visible:ring-blue-500/75">
                        <span className="flex items-center gap-2">
                            {title}:
                            <strong className="font-semibold text-blue-600">{selectedOptionName}</strong>
                        </span>
                        <ChevronUpIcon className={`${open ? 'rotate-180 transform' : ''} h-5 w-5 text-gray-500`} />
                    </Disclosure.Button>
                    <Transition
                        as={Fragment}
                        enter="transition ease-out duration-100"
                        enterFrom="transform opacity-0 scale-95"
                        enterTo="transform opacity-100 scale-100"
                        leave="transition ease-in duration-75"
                        leaveFrom="transform opacity-100 scale-100"
                        leaveTo="transform opacity-0 scale-95"
                    >
                        <Disclosure.Panel className="px-4 pt-4 pb-2 text-sm text-gray-500">
                            <input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder={`Buscar ${title.toLowerCase()}...`}
                                className="w-full form-input rounded-md shadow-sm text-sm mb-2"
                                // Evita que al presionar 'espacio' se cierre el disclosure
                                onKeyDown={(e) => e.stopPropagation()}
                            />
                            <div className="max-h-48 overflow-y-auto">
                                <button
                                    onClick={() => onSelect('')}
                                    className={`${selectedValue === '' ? 'bg-blue-500 text-white' : 'text-gray-900'} block w-full text-left rounded-md px-3 py-1.5 text-sm hover:bg-gray-100`}
                                >
                                    Todos
                                </button>
                                {filteredOptions.map(option => (
                                    <button
                                        key={option.id}
                                        onClick={() => onSelect(option.id)}
                                        className={`${selectedValue == option.id ? 'bg-blue-500 text-white' : 'text-gray-900'} block w-full text-left rounded-md px-3 py-1.5 text-sm hover:bg-gray-100`}
                                    >
                                        {option.name}
                                    </button>
                                ))}
                            </div>
                        </Disclosure.Panel>
                    </Transition>
                </>
            )}
        </Disclosure>
    );
}