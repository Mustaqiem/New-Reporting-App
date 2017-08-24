<?php
namespace App\Models;

class Item extends BaseModel
{
    protected $table = 'items';
    protected $column = ['id', 'name'];
    protected $joinTable = 'groups';

    public function create($data)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'name'        => $data['name'],
            'description' => $data['description'],
            'recurrent'   => $data['recurrent'],
            'start_date'  => $data['start_date'],
            'group_id'    => $data['group_id'],
            'user_id'     => $data['user_id'],
            'creator'     => $data['creator'],
            'privacy'     => $data['privacy'],
            'status'      => $data['status'],
            'reported_at' => $data['reported_at'],
            'updated_at'  => $date
        ];
        $this->createData($data);
        return $this->db->lastInsertId();
    }

    public function update($data, $id)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'name'        => $data['name'],
            'description' => $data['description'],
            'recurrent'   => $data['recurrent'],
            'start_date'  => $data['start_date'],
            'group_id'    => $data['group_id'],
            'user_id'     => $data['user_id'],
            'public'      => $data['public'],
            'updated_at'  => $date
        ];
        $this->updateData($data, $id);
    }

    // public function getAllItem()
    // {
    //     $qb = $this->db->createQueryBuilder();
    //     $qb->select('gr.name as groups', 'it.*')
    //        ->from($this->table, 'it')
    //        ->join('it', $this->joinTable, 'gr', 'gr.id = it.group_id')
    //        ->where('it.deleted = 0');
    //        $result = $qb->execute();
    //        return $result->fetchAll();
    // }

    public function getAllDeleted()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('gr.name as groups', 'it.*')
        ->from($this->table, 'it')
        ->join('it', $this->joinTable, 'gr', 'gr.id = it.group_id')
        ->where('it.deleted = 1');
        $result = $qb->execute();
        return $result->fetchAll();
    }

    public function getUnreportedUserItemInGroup($userId, $groupId)
    {
        $qb = $this->db->createQueryBuilder();
        $query1 = $qb->select('item_id')
        ->from('reported_item')
        ->where('user_id =' . $userId)
        ->execute();

        $qb1 = $this->db->createQueryBuilder();
        if ($query1->fetchAll()[0] != NULL) {
            $this->query = $qb1->select('i.*')
            ->from($this->table, 'i')
            ->join('i', 'reported_item', 'r', $qb1->expr()->notIn('i.id', $query1))
            ->where('i.user_id = '. $userId .'&&'. 'i.group_id = '. $groupId)
            ->orWhere('i.group_id = '. $groupId)
            ->andWhere('i.deleted = 0 && i.status = 0')
            ->groupBy('i.id');
        } else {
            $this->query = $qb1->select('*')
            ->from($this->table)
            ->where('user_id = '. $userId .'&&'. 'group_id = '. $groupId)
            ->orWhere('group_id = '. $groupId)
            ->andWhere('deleted = 0 && status = 0');
        }
        return $this->fetchAll();
    }

    public function getReportedUserItemInGroup($userId, $groupId)
    {
        $qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('*')
        ->from($this->table)
        ->where('user_id = '. $userId .'&&'. 'group_id = '. $groupId)
        ->andWhere('deleted = 0 && status = 1');
        return $this->fetchAll();
    }

    public function getUserInGroupItem($userId)
    {
        $qb = $this->db->createQueryBuilder();
        $query = $qb->select('group_id')
        ->from('user_group')
        ->where('user_id =' . $userId)
        ->execute();

        $qb1 = $this->db->createQueryBuilder();
        $query1 = $qb1->select('i.*')
        ->from($this->table, 'i')
        ->where('i.status = 0')
        ->join('i', 'user_group', 'ug', $qb1->expr()->in('i.group_id',$query))
        ->execute();
        $result2  = array_map("unserialize", array_unique(array_map("serialize", $query1->fetchAll())));
        return $result2;
    }

    public function getAllGroupItem($userId)
    {
        $qb = $this->db->createQueryBuilder();
        $query = $qb->select('group_id')
        ->from('user_group')
        ->where('user_id =' . $userId)
        ->execute();

        $qb1 = $this->db->createQueryBuilder();
        $query1 = $qb1->select('i.*', 'u.username as user', 'u.image as user_image', 'c.comment',
                'us.username as creator', 'us.image as creator_image','gr.name as group_name', 'img.image')
                ->from($this->table, 'i')
                ->where('i.deleted = 0')
                ->andWhere('i.privacy = 0')
                ->join('i', 'user_group', 'ug', $qb1->expr()->in('i.group_id',$query))
                ->leftJoin('i', 'users', 'u', 'i.user_id = u.id')
                ->leftJoin('i', 'users', 'us', 'i.creator = us.id')
                ->leftJoin('i', 'groups', 'gr', 'i.group_id = gr.id')
                ->leftJoin('i', 'image_item', 'img', 'i.id = img.item_id')
                ->leftJoin('i', 'comments', 'c', 'i.id = c.item_id')
                ->orderBy('i.updated_at', 'desc')
                ->execute();

        return  $query1->fetchAll();

    }

    public function getUserItemInGroup($userId)
    {
        $qb = $this->db->createQueryBuilder();
        $query1 = $qb->select('item_id')
        ->from('reported_item', 'ri')
        ->where('ri.user_id =' . $userId)
        ->execute();

        $qb2 = $this->db->createQueryBuilder();
        $query2 = $qb2->select('group_id')
        ->from('user_group', 'ug')
        ->where('ug.user_id =' . $userId)
        ->execute();

        $qb1 = $this->db->createQueryBuilder();
        if ($query1->fetchAll() != NULL) {
            $this->query = $qb1->select('i.*')
            ->from($this->table, 'i')
            ->join('i', 'reported_item', 'r', $qb1->expr()->notIn('i.id', $query1))
            ->where('i.user_id ='.$userId)
            ->andWhere('i.deleted = 0')
            ->groupBy('i.id');
        } else {
            $this->query = $qb1->select('i.*')
            ->from($this->table, 'i')
            ->join('i', 'user_group', 'ug', $qb1->expr()->in('i.group_id', $query2))
            ->where('i.user_id ='.$userId)
            ->andWhere('i.deleted = 0');
        }

        return $this->fetchAll();
    }

    public function userItem($userId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
        ->from($this->table)
        ->where('user_id = '. $userId)
        ->andWhere('status = 0');

        $result = $qb->execute();
        return $result->fetchAll();
    }

    public function getItem($column1, $value1, $column2, $value2)
    {
        $param1 = ':'.$column1;
        $param2 = ':'.$column2;
        $qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('*')
        ->from($this->table)
        ->setParameter($param1, $value1)
        ->setParameter($param2, $value2)
        ->where($column1 . ' = '. $param1. '&&' . $column2 . ' = '. $param2);
        // ->execute();
        return $this;
        // $result = $qb->execute();
        // return $result->fetchAll();
    }

    public function getAllItem()
    {
        $qb = $this->db->createQueryBuilder();

        $this->query = $qb->select('*')
        ->from($this->table)
        ->where('deleted = 0');

        return $this;
    }

    public function getReportedItemDetail($id)
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('it.*',
        //  'it.created_at as created', 'it.reported_at as reported','it.description', 'it.name as item',
         'u.username as user', 'img.image',
          'c.comment', 'u.image as user_image', 'us.image as creator_image',
           'us.username as creator', 'g.name as group_name', 'g.id as group_id')
        ->from($this->table, 'it')
        ->join('it', 'users', 'u', 'u.id = it.user_id')
        ->leftJoin('it', 'users', 'us', 'it.creator = us.id')
        ->leftJoin('it', 'image_item', 'img', 'it.id = img.item_id')
        ->leftJoin('it', 'comments', 'c', 'it.id = c.item_id')
        ->leftJoin('it', 'groups', 'g', 'g.id = it.group_id')
        ->where('it.id = :id')
        ->andWhere('it.privacy = 0')
        ->setParameter(':id', $id);

        $result = $qb->execute();
        return $result->fetchAll();

    }

    public function getUnreportedItemDetail($id)
    {

        $qb = $this->db->createQueryBuilder();

        $qb->select('it.*',
        // 'it.created_at as created', 'it.reported_at as reported', 'it.description', 'it.name as item',
         'u.username as user', 'img.image',
          'c.comment', 'u.image as user_image', 'us.image as creator_image',
           'us.username as creator', 'g.name as group_name', 'g.id as group_id')
        ->from($this->table, 'it')
        ->join('it', 'users', 'u', 'u.id = it.creator')
        ->leftJoin('it', 'users', 'us', 'it.creator = us.id')
        ->leftJoin('it', 'image_item', 'img', 'it.id = img.item_id')
        ->leftJoin('it', 'comments', 'c', 'it.id = c.item_id')
        ->leftJoin('it', 'groups', 'g', 'g.id = it.group_id')
        ->where('it.id = :id')
        ->andWhere('it.privacy = 0')
        ->andWhere('it.user_id is null')
        ->setParameter(':id', $id);

        $result = $qb->execute();
        return $result->fetchAll();

    }

    public function getByMonth($month, $year, $user_id)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
            ->from($this->table)
            ->where('YEAR(updated_at) = :year')
            ->andWhere('MONTH(updated_at) = :month')
            ->andWhere('user_id = :id')
            ->andWhere('status = 1');

        $qb->setParameter('year', $year)
            ->setParameter('month', $month)
           ->setParameter('id', $user_id);

        $query = $qb->execute();
        return $query->fetchAll();
    }

    public function getByYear($year, $user_id)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
            ->from($this->table)
            ->where('YEAR(updated_at) = :year')
            ->andWhere('user_id = :id')
            ->andWhere('status = 1');

        $qb->setParameter('year', $year)
           ->setParameter('id', $user_id);

        $query = $qb->execute();
        return $query->fetchAll();
    }


}
