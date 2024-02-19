<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function Welcome()
    {
        // Récupérer toutes les catégories
        $categories = Categorie::all();

        // Récupérer tous les produits avec leurs catégories correspondantes paginés par 10 par page
        $productsWithCategories = Product::with('category')->paginate(10);

        return view('welcome', compact('categories', 'productsWithCategories'));
    }

    public function erreur404()
    {

        return view('404');
    }
    public  function testimonial()
    {
        return view('testimonial');
    }
    public function contact()
    {
        return view('contact');
    }



    public function editProfile()
    {
        $user = auth()->user();

        return view('editProfiles', compact('user'));
    }


    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        // Validez les données du formulaire
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'profile_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'quarter' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'secondary_phone_number' => 'nullable|string|max:20',
        ]);

        // Mettez à jour les champs du modèle utilisateur avec les données du formulaire
        $user->name = $request->input('name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->address = $request->input('address');
        $user->quarter = $request->input('quarter');
        $user->phone_number = $request->input('phone_number');
        $user->secondary_phone_number = $request->input('secondary_phone_number');

        // Mettez à jour l'image de profil si elle est fournie
        if ($request->hasFile('profile_image')) {
            // Supprimez l'ancienne image de profil s'il y en a
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Enregistrez la nouvelle image de profil dans un répertoire non accessible au public
            $imagePath = $request->file('profile_image')->store('private/images');
            $user->profile_image = $imagePath;
        }
        // Sauvegardez les modifications dans la base de données
        $user->save();

        // Redirigez l'utilisateur vers la page de modification de profil avec un message de succès
        return redirect()->route('user.profile')->with('success', 'Profil mis à jour avec succès !');
    }


    public function showProduct($id)
    {
        // Récupérer le produit avec ses catégories
        $product = Product::findOrFail($id);
        $productsWithCategories = $product->categories()->get();
        // Passer les données à la vue
        return view('showProduct', ['product' => $product, 'productsWithCategories' => $productsWithCategories]);
    }


    public function profile()
    {
        $user = auth()->user();
        return view('profile', compact('user'));
    }
}
