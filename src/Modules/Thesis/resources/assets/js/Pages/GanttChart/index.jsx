import { React, useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import GanttChart from './components/GanttChart';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { endOfDay, startOfDay, parseISO } from 'date-fns';

// --- Componente de Botón estilo Material-UI "contained" y "outlined" ---
function StyledButton({ children, onClick, variant = 'contained', ...props }) {
    const baseClasses = "inline-flex items-center justify-center rounded text-sm font-medium uppercase tracking-wider px-4 py-2 transition-all duration-200 ease-in-out shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed";
    
    const variants = {
        // Estilo "contained" (botón principal, sólido)
        contained: "bg-blue-600 text-white hover:bg-blue-700 hover:shadow-md focus:ring-blue-500",
        // Estilo "outlined" (botón secundario, con borde)
        outlined: "border border-gray-400 text-gray-700 bg-transparent hover:bg-gray-100 focus:ring-gray-400 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-800"
    };

    return (
        <button className={`${baseClasses} ${variants[variant]}`} onClick={onClick} {...props}>
            {children}
        </button>
    );
}

// --- Componente para Inputs y Selects estilo Material-UI "outlined" ---
// Usaremos un wrapper para crear el efecto del label "flotante" y el borde.
function MaterialStyledField({ label, htmlFor, children, className }) {
    return (
        <div className={`relative ${className}`}>
            {children}
            <label
                htmlFor={htmlFor}
                className="absolute text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 left-1
                peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500
                peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2
                peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4"
            >
                {label}
            </label>
        </div>
    );
}


export default function GanttChartIndex({ auth, ganttData: initialData }) {
    const [filters, setFilters] = useState({
        studentName: '',
        statusId: '',
        startDate: '', 
        endDate: '',
    });
    const filteredData = useMemo(() => {
        // Si no hay datos iniciales, devuelve un array vacío
        if (!initialData) return [];

        let data = initialData;

        if (filters.studentName) {
            data = data.filter(student => student.student_name === filters.studentName);
        }
        if (filters.statusId) {
            data = data.filter(student => student.statuses.at(-1)?.status_name === filters.statusId);
        }
        if (filters.startDate || filters.endDate) {
             data = data.filter(student =>
                student.statuses.some(status => {
                    const statusStart = parseISO(status.start);
                    const statusEnd = parseISO(status.end);
                    const filterStart = filters.startDate ? startOfDay(parseISO(filters.startDate)) : null;
                    const filterEnd = filters.endDate ? endOfDay(parseISO(filters.endDate)) : null;
                    const startsAfterFilterEnd = filterEnd && statusStart > filterEnd;
                    const endsBeforeFilterStart = filterStart && statusEnd < filterStart;
                    return !(startsAfterFilterEnd || endsBeforeFilterStart);
                })
            );
        }
        return data;
    }, [initialData, filters]); // Se recalcula automáticamente si initialData o filters cambia

    // Opciones para los selectores, calculadas una sola vez
    const studentOptions = useMemo(() => {
        const uniqueStudents = [...new Map(initialData.map(item => [item.student_id, item])).values()];
        return uniqueStudents.map(student => ({ value: student.student_name, label: student.student_name }));
    }, [initialData]);

    const statusOptions = useMemo(() => {
        const allStatuses = initialData.flatMap(student => student.statuses);
        const uniqueStatuses = [...new Map(allStatuses.map(item => [item.status_name, item])).values()];
        return uniqueStatuses.map(status => ({ value: status.status_name, label: status.status_name }));
    }, [initialData]);
    
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => ({ ...prev, [name]: value }));
    };

    
    const handleClear = () => {
        setFilters({ studentName: '', statusId: '', startDate: '', endDate: '' });
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Diagrama General de Tesistas" />

            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h2 className="text-2xl font-bold tracking-tight">
                        Vista General de Proyectos de Tesis
                    </h2>
                </div>
                
                <Card className="w-full shadow-sm">
                     <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 pt-4">
                            
                            <MaterialStyledField label="Estudiante" htmlFor="studentName">
                                <select 
                                    id="studentName"
                                    name="studentName"
                                    value={filters.studentName}
                                    onChange={handleFilterChange}
                                    // El placeholder vacío es necesario para que el label funcione bien
                                    placeholder=" "
                                    className="block w-full rounded border-gray-400 focus:border-blue-600 focus:ring-0 peer h-10 px-2.5 text-sm"
                                >
                                    <option value="">Todos los estudiantes</option>
                                    {studentOptions.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                                </select>
                            </MaterialStyledField>

                            <MaterialStyledField label="Estado Final" htmlFor="statusId">
                                <select 
                                    id="statusId"
                                    name="statusId"
                                    value={filters.statusId}
                                    onChange={handleFilterChange}
                                    placeholder=" "
                                    className="block w-full rounded border-gray-400 focus:border-blue-600 focus:ring-0 peer h-10 px-2.5 text-sm"
                                >
                                    <option value="">Todos los estados</option>
                                    {statusOptions.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                                </select>
                            </MaterialStyledField>
                            
                            <MaterialStyledField label="Desde" htmlFor="startDate">
                                <input
                                    id="startDate"
                                    name="startDate"
                                    type="date"
                                    value={filters.startDate}
                                    onChange={handleFilterChange}
                                    placeholder=" "
                                    className="block w-full rounded border-gray-400 focus:border-blue-600 focus:ring-0 peer h-10 px-2.5 text-sm"
                                />
                            </MaterialStyledField>
                            
                            <MaterialStyledField label="Hasta" htmlFor="endDate">
                                <input
                                    id="endDate"
                                    name="endDate"
                                    type="date"
                                    value={filters.endDate}
                                    onChange={handleFilterChange}
                                    placeholder=" "
                                    className="block w-full rounded border-gray-400 focus:border-blue-600 focus:ring-0 peer h-10 px-2.5 text-sm"
                                />
                            </MaterialStyledField>
                        </div>

                         <div className="flex justify-end gap-3 mt-6">
                            <StyledButton variant="outlined" onClick={handleClear}>Limpiar</StyledButton>
                        </div>
                    </CardContent>
                </Card>

                <Card className="w-full shadow-sm">
                    <CardHeader>
                        <CardTitle>Cronograma de Estados por Estudiante</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {filteredData && filteredData.length > 0 ? (
                            <GanttChart data={filteredData} />
                        ) : (
                            <div className="flex items-center justify-center h-40">
                                <p className="text-sm text-muted-foreground">
                                    No se encontraron resultados para los filtros aplicados.
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}