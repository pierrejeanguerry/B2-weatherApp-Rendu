Pour lancer le back il suffit de le lancer avec la commande:

```bash
docker-compose up -d
```

Pour générer des fixtures, il faut aller dans le container contenant le serveur Symfony:

```bash
docker exec -it back bash
```

puis, lancer la commande suivante:

```bash
symfony console doctrine:fixture:load
```

l'utilisateur de test ainsi généré à pour identifiants:

```
pj@pj.fr
123456789Pj
```
