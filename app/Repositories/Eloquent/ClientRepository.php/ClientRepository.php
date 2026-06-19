<?php

namespace Appepositories\Contracts;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface extends RepositoryInterface
{
    /**
     * استرداد جميع العملاء.
     *
     * @return Collection<int, Client>
     */
    public function getAllClients(): Collection;

    /**
     * استرداد عميل بواسطة المعرف الخاص به.
     *
     * @param int $id معرف العميل.
     * @return Client|null العميل المطلوب أو null إذا لم يتم العثور عليه.
     */
    public function getClientById(int $id): ?Client;
}
--- FILE SEPARATOR ---
<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    /**
     * ClientRepository constructor.
     *
     * @param Client $model نموذج العميل المرتبط بالمستودع.
     */
    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    /**
     * استرداد جميع العملاء.
     *
     * @return Collection<int, Client>
     */
    public function getAllClients(): Collection
    {
        return $this->model->all();
    }

    /**
     * استرداد عميل بواسطة المعرف الخاص به.
     *
     * @param int $id معرف العميل.
     * @return Client|null العميل المطلوب أو null إذا لم يتم العثور عليه.
     */
    public function getClientById(int $id): ?Client
    {
        return $this->model->find($id);
    }
}
