import React from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

export default function Edit({ auth, movementType }) {
	const { t } = useTranslation(["translation", "common"]);
	const { data, setData, put, processing, errors } = useForm({
		name: movementType.name || "",
		description: movementType.description || "",
	});

	const submit = (e) => {
		e.preventDefault();
		put(route("admin.item-movement-type.update", movementType.id));
	};

	return (
		<AdminLayout auth={auth}>
			<Head title="Editar Tipo de Movimiento" />
			<div>
				<h1 className="text-2xl font-semibold mb-4">Editar Tipo de Movimiento</h1>
				<form onSubmit={submit} className="space-y-4 max-w-2xl">
					<div>
						<label htmlFor="name" className="block font-medium text-sm text-gray-700">
							Nombre
						</label>
						<input
							id="name"
							type="text"
							className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
							value={data.name}
							onChange={(e) => setData("name", e.target.value)}
						/>
						{errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
					</div>

					<div>
						<label htmlFor="description" className="block font-medium text-sm text-gray-700">
							Descripción
						</label>
						<textarea
							id="description"
							className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
							value={data.description}
							onChange={(e) => setData("description", e.target.value)}
						/>
						{errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
					</div>

					<div className="flex gap-4">
						<button type="submit" disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
							Actualizar
						</button>
						<Link href={route('admin.item-movement-type.index')} className="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
							Cancelar
						</Link>
					</div>
				</form>
			</div>
		</AdminLayout>
	);
}
